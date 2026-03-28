<?php
// ------------------------------
// CF Captcha (переписано под PHP 8+, сессии и логами)
// ------------------------------

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Настройки
define('CAPTCHA_WIDTH', 130);
define('CAPTCHA_HEIGHT', 35);
define('CAPTCHA_LENGTH', 5);
define('CAPTCHA_FONT_PATH', __DIR__ . '/font/');
define('CAPTCHA_FONT_LIST', ['arial.ttf', 'verdana.ttf']);
define('CAPTCHA_LOG', __DIR__ . '/../logs/captcha_debug.log');

// Проверка капчи
function check_captcha(string $input): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $input = strtolower(trim($input));
    $valid = strtolower($_SESSION['captcha'] ?? '');

    // Лог
    file_put_contents(CAPTCHA_LOG, "[" . date("Y-m-d H:i:s") . "] Введено: {$input}, Ожидалось: {$valid}\n", FILE_APPEND);

    return $input !== '' && $valid !== '' && $input === $valid;
}

// Генерация капчи (если запрошено изображение)
if (isset($_GET['img'])) {
    generate_captcha_image();
    exit;
}

// Функция генерации изображения и текста
function generate_captcha_image(): void {
    $image = imagecreatetruecolor(CAPTCHA_WIDTH, CAPTCHA_HEIGHT);

    // Цвет фона
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgColor);

    // Шрифт
    $font = CAPTCHA_FONT_PATH . CAPTCHA_FONT_LIST[array_rand(CAPTCHA_FONT_LIST)];
    $captchaText = '';
    $chars = str_split('abcdefghjkmnpqrstuvwxyz23456789');

    $x = 10;

    for ($i = 0; $i < CAPTCHA_LENGTH; $i++) {
        $letter = $chars[random_int(0, count($chars) - 1)];
        $captchaText .= $letter;

        $fontSize = random_int(16, 20);
        $angle = random_int(-20, 20);
        $y = random_int(24, 30);
        $color = imagecolorallocate($image, random_int(0, 100), random_int(0, 100), random_int(0, 100));

        imagettftext($image, $fontSize, $angle, $x, $y, $color, $font, $letter);
        $x += 20;
    }

    // Сохраняем капчу в сессию
    $_SESSION['captcha'] = $captchaText;

    // Лог
    file_put_contents(CAPTCHA_LOG, "[" . date("Y-m-d H:i:s") . "] Сгенерирована капча: {$captchaText}\n", FILE_APPEND);

    // Отдаём изображение
    header("Content-type: image/png");
    imagepng($image);
    imagedestroy($image);
}