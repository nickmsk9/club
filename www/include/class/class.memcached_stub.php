<?php

if (!class_exists('Memcached')) {
    /**
     * Лёгкий fallback для локальной разработки без расширения memcached.
     * Хранит значения только в памяти текущего PHP-процесса.
     */
    class Memcached
    {
        public const OPT_COMPRESSION = 1;
        public const OPT_DISTRIBUTION = 9;
        public const OPT_CONNECT_TIMEOUT = 14;
        public const OPT_RETRY_TIMEOUT = 15;
        public const DISTRIBUTION_CONSISTENT = 1;
        public const RES_SUCCESS = 0;
        public const RES_NOTFOUND = 16;
        public const RES_FAILURE = 1;

        /** @var array<string, mixed> */
        protected array $data = [];

        /** @var array<int, array{host:string, port:int}> */
        protected array $servers = [];

        protected int $resultCode = self::RES_SUCCESS;

        /** @var array<int, mixed> */
        protected array $options = [];

        public function __construct(?string $persistent_id = null)
        {
        }

        public function setOption(int $option, mixed $value): bool
        {
            $this->options[$option] = $value;
            return true;
        }

        public function addServer(string $host, int $port, int $weight = 0): bool
        {
            $this->servers[] = ['host' => $host, 'port' => $port];
            $this->resultCode = self::RES_SUCCESS;
            return true;
        }

        public function getServerList(): array
        {
            return $this->servers;
        }

        public function get(string $key, ?callable $cache_cb = null, int $get_flags = 0): mixed
        {
            if (!array_key_exists($key, $this->data)) {
                $this->resultCode = self::RES_NOTFOUND;
                return false;
            }

            $this->resultCode = self::RES_SUCCESS;
            return $this->data[$key];
        }

        public function set(string $key, mixed $value, int $expiration = 0): bool
        {
            $this->data[$key] = $value;
            $this->resultCode = self::RES_SUCCESS;
            return true;
        }

        public function delete(string $key, int $time = 0): bool
        {
            if (!array_key_exists($key, $this->data)) {
                $this->resultCode = self::RES_NOTFOUND;
                return false;
            }

            unset($this->data[$key]);
            $this->resultCode = self::RES_SUCCESS;
            return true;
        }

        public function increment(string $key, int $offset = 1, int $initial_value = 0, int $expiry = 0): int|false
        {
            if (!array_key_exists($key, $this->data)) {
                $this->data[$key] = $initial_value;
            }

            if (!is_numeric($this->data[$key])) {
                $this->resultCode = self::RES_FAILURE;
                return false;
            }

            $this->data[$key] += $offset;
            $this->resultCode = self::RES_SUCCESS;
            return (int)$this->data[$key];
        }

        public function flush(int $delay = 0): bool
        {
            $this->data = [];
            $this->resultCode = self::RES_SUCCESS;
            return true;
        }

        public function getStats(?string $type = null): array
        {
            return [];
        }

        public function getVersion(): array
        {
            return [];
        }

        public function getResultCode(): int
        {
            return $this->resultCode;
        }

        public function quit(): bool
        {
            return true;
        }
    }
}

if (!class_exists('Memcache')) {
    /**
     * Совместимость со старым расширением Memcache.
     */
    class Memcache extends Memcached
    {
        public function connect(string $host, int $port = 11211, int $timeout = 0): bool
        {
            return $this->addServer($host, $port);
        }

        public function set(string $key, mixed $value, int $flag = 0, int $expiration = 0): bool
        {
            return parent::set($key, $value, $expiration);
        }
    }
}
