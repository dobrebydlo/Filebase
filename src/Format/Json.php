<?php namespace Dobrebydlo\Filebase\Format;


class Json implements FormatInterface
{
    /**
     * @return string
     */
    public static function getFileExtension(): string
    {
        return 'json';
    }

    /**
     * @param array $data
     * @param bool $pretty
     * @return string
     * @throws FormatException
     */
    public static function encode(?array $data = [], bool $pretty = true): string
    {
        $options = 0;
        if ($pretty == true) {
            $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        $encoded = json_encode((array)$data, $options);
        if ($encoded === false) {
            throw new EncodingException(
                "json_encode: '" . json_last_error_msg() . "'",
                0,
                null,
                $data
            );
        }

        return (string)$encoded;
    }

    /**
     * @param string $data
     * @return array
     * @throws FormatException
     */
    public static function decode(?string $data): array
    {
        if ($data === null || mb_strlen($data = trim($data)) === 0) {

            throw new DecodingException('Empty data');

        } else {

            $decoded = json_decode($data, true);

            if ($decoded === null) {
                throw new DecodingException(
                    "json_decode: '" . json_last_error_msg() . "'",
                    0,
                    null,
                    $data
                );
            }
        }

        return isset($decoded) && is_array($decoded) ? $decoded : [];
    }
}
