<?php

class jSEND
{
    /**
     * Распаковка и декодирование строки
     */
    public function getData($s)
    {
        // Разделение строки на две части, если есть символ ==
        [$s1, $s2] = explode('==', $s) + [null, null];

        $tmp1 = self::decompressLZW(self::decodeBinary(self::decode847($s1)));
        $tmp2 = $s2 ? self::decompressLZW(self::decodeBinary(self::decode847($s2))) : '';

        $data = '';
        $unicodeMap = [
            128 => 8364, 130 => 8218, 131 => 402, 132 => 8222, 133 => 8230, 134 => 8224,
            135 => 8225, 136 => 710, 137 => 8240, 138 => 352, 139 => 8249, 140 => 338,
            142 => 381, 145 => 8216, 146 => 8217, 147 => 8220, 148 => 8221, 149 => 8226,
            150 => 8211, 151 => 8212, 152 => 732, 153 => 8482, 154 => 353, 155 => 8250,
            156 => 339, 158 => 382, 159 => 376
        ];

        if (!empty($tmp2)) {
            for ($i = 0, $len = strlen($tmp1); $i < $len; $i++) {
                $char1 = $tmp1[$i] ?? '';
                $char2 = $tmp2[$i] ?? '';

                $ord2 = ord($char2);
                $ord1 = ord($char1);

                if ($ord2 !== 224) {
                    $data .= self::unichr($ord1 + 256 * $ord2);
                } else {
                    $data .= ($ord1 > 127) ? utf8_encode($char1) : $char1;
                }
            }
        } else {
            $data = utf8_encode($tmp1);
        }

        // Замена ANSI символов (128-159) на корректные юникодные символы
       foreach ($unicodeMap as $k => $v) {
    $search = mb_convert_encoding('&#' . $k . ';', 'UTF-8', 'HTML-ENTITIES');
    $replace = self::unichr($v);
    $data = str_replace($search, $replace, $data);
}

        return substr($data, 1); // удаляем лишний первый байт
    }

    /**
     * Декодирование 847-кодировки (внутренний формат)
     */
    private static function decode847($input)
    {
        $byte = 7;
        $mask = 0;
        $codes = [];

        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $value = ord($input[$i]);

            if ($value === 61 && isset($input[$i + 1])) {
                $i++;
                $value = ord($input[$i]) - 16;
            }

            if ($byte > 6) {
                $mask = $value;
                $byte = 0;
            } else {
                $bit = 1 << $byte;
                if (($mask & $bit) === $bit) {
                    $value += 128;
                }

                $codes[] = $value;
                $byte++;
            }
        }

        return $codes;
    }

    /**
     * Декодирование битового потока
     */
    private static function decodeBinary(array $codes)
    {
        $result = [];
        $dictSize = 256;
        $bits = 8;
        $buffer = 0;
        $bufferLen = 0;

        foreach ($codes as $code) {
            $buffer = ($buffer << 8) + $code;
            $bufferLen += 8;

            while ($bufferLen >= $bits) {
                $bufferLen -= $bits;
                $result[] = $buffer >> $bufferLen;
                $buffer &= (1 << $bufferLen) - 1;
                $dictSize++;
                if ($dictSize >> $bits) {
                    $bits++;
                }
            }
        }

        return $result;
    }

    /**
     * Распаковка LZW (сжатые данные)
     */
    private static function decompressLZW(array $codes)
    {
        $output = '';
        $dict = range("\x00", "\xff");
        $prevWord = '';

        foreach ($codes as $index => $code) {
            $entry = $dict[$code] ?? ($prevWord . $prevWord[0] ?? '');

            $output .= $entry;

            if ($index > 0) {
                $dict[] = $prevWord . $entry[0];
            }

            $prevWord = $entry;
        }

        return $output;
    }

    /**
     * Юникод-символ по числовому коду
     */
    private static function unichr($code)
    {
        if ($code <= 0x7F) {
            return chr($code);
        } elseif ($code <= 0x7FF) {
            return chr(0xC0 | ($code >> 6)) . chr(0x80 | ($code & 0x3F));
        } elseif ($code <= 0xFFFF) {
            return chr(0xE0 | ($code >> 12)) .
                   chr(0x80 | (($code >> 6) & 0x3F)) .
                   chr(0x80 | ($code & 0x3F));
        } elseif ($code <= 0x10FFFF) {
            return chr(0xF0 | ($code >> 18)) .
                   chr(0x80 | (($code >> 12) & 0x3F)) .
                   chr(0x80 | (($code >> 6) & 0x3F)) .
                   chr(0x80 | ($code & 0x3F));
        }

        return ''; // недопустимое значение
    }
}