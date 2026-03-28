<?php
// Если класс mc_chat уже загружен, пропускаем повторную загрузку
if (class_exists('mc_chat')) {
    return;
}

/**
 * Класс mc_chat — реализация кэшируемого чата с хранением сообщений в Memcached
 */
class mc_chat
{
    protected $chan;  // Имя канала чата (префикс ключей)
    protected $mc;    // Объект Memcached
    protected $ret;   // Количество последних сообщений для хранения

    /**
     * Конструктор
     *
     * @param Memcached $memcached Объект memcached
     * @param string    $channel   Имя канала
     * @param int       $retention Количество хранимых сообщений
     */
    public function __construct(Memcached $memcached, string $channel, int $retention = 5)
    {
        $this->mc   = $memcached;
        $this->chan = $channel;
        $this->ret  = $retention;
    }

    /**
     * Получение всех сообщений с минимального до максимального ID
     *
     * @param int $from ID, с которого начинать (не используется)
     * @return array Массив сообщений, отсортированных от новых к старым
     */
    public function messages(int $from = 0): array
    {
        $max = (int)$this->mc->get("{$this->chan}:max:posted");
        $min = (int)$this->mc->get("{$this->chan}:min:posted");

        if ($min > $max || $min <= 0) {
            $min = 1;
        }
        if ($max <= 0) {
            return [];
        }

        // Собираем ключи сообщений
        $keys = [];
        for ($i = $min; $i <= $max; $i++) {
            $keys[] = "{$this->chan}:msg:{$i}";
        }

        // Пакетное получение всех сообщений
        $items = $this->mc->getMulti($keys) ?: [];
        $messages = [];

        foreach ($keys as $key) {
            if (isset($items[$key]['user'], $items[$key]['message'])
                && $items[$key]['user'] !== ''
                && $items[$key]['message'] !== ''
            ) {
                $messages[] = $items[$key];
            }
        }

        // Возвращаем в обратном порядке (новые сообщения первыми)
        return array_reverse($messages);
    }

    /**
     * Получение одного сообщения по ID
     *
     * @param int $id
     * @return array|null
     */
    public function get(int $id): ?array
    {
        $data = $this->mc->get("{$this->chan}:msg:{$id}");
        return is_array($data) ? $data : null;
    }

    /**
     * Добавление нового сообщения
     *
     * @param int    $user
     * @param string $username
     * @param int    $class
     * @param string $warned
     * @param string $donor
     * @param string $gender
     * @param string $parked
     * @param string $message
     * @param int    $time
     */
    public function add(
        int $user,
        string $username,
        int $class,
        string $warned,
        string $donor,
        string $gender,
        string $parked,
        string $message,
        int $time
    ): void {
        // Атомарная блокировка для предотвращения гонок
        $lockKey = "{$this->chan}:lock";
        $start = microtime(true);
        while (!$this->mc->add($lockKey, 1, 5)) {
            if (microtime(true) - $start > 5) {
                break;
            }
            usleep(50000);
        }

        // Получаем новый ID сообщения
        $id = (int)$this->mc->increment("{$this->chan}:max:posted");
        if ($id <= 0) {
            $id = 1;
            $this->mc->set("{$this->chan}:max:posted", 1);
        }

        // Подготавливаем данные
        $data = [
            'id'       => $id,
            'user'     => $user,
            'username' => $username,
            'class'    => $class,
            'warned'   => $warned,
            'donor'    => $donor,
            'gender'   => $gender,
            'parked'   => $parked,
            'time'     => $time,
            'message'  => $message,
        ];

        // Сохраняем в Memcached
        $this->mc->set("{$this->chan}:msg:{$id}", $data, 86400);

        // Поддерживаем размер очереди
        if ($id > $this->ret) {
            if (!$this->mc->increment("{$this->chan}:min:posted")) {
                $this->mc->set("{$this->chan}:min:posted", 1);
            }
        }

        // Снимаем блокировку
        $this->mc->delete($lockKey);
    }

    /**
     * Удаление сообщения по ID
     *
     * @param int $id
     */
    public function purne(int $id): void
    {
        $this->mc->delete("{$this->chan}:msg:{$id}");
    }
}
?>