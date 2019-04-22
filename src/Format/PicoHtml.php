<?php namespace Ffdb\Format;

use Symfony\Component\Yaml\Yaml as YamlParser;

class PicoHtml implements FormatInterface
{

    protected static $rootKeys = ['__created_at', '__updated_at'];

    /**
     * @return string
     */
    public static function getFileExtension(): string
    {
        return 'html';
    }

    /**
     * @param array $data
     * @return string
     */
    public static function encode(?array $data = []): string
    {
        $encoded = '';
        $data = (array)$data;

        if (array_key_exists('data', $data)) {
            if (is_array($data['data']) || is_object($data['data'])) {
                $item_data = (array)$data['data'];
                unset($data['data']);
                $snake_keys = array_map([static::class, 'snakeCase'], array_keys($item_data));
                $item_data = array_combine($snake_keys, array_values($item_data));
                $data = array_replace($item_data, $data);
            }
        }

        if (array_key_exists('content', $data)) {
            if (is_string($data['content'])) {
                $encoded .= $data['content'];
            }
            unset($data['content']);
        }

        try {
            $yaml = YamlParser::dump($data);
        } catch (\Exception $exception) {
            $yaml = '';
        }

        if (mb_strlen($yaml) > 0) {
            $encoded =
<<<ENCODED
---
{$yaml}---
{$encoded}
ENCODED;
        }

        return $encoded;
    }

    /**
     * @param $data
     * @return array
     */
    public static function decode(?string $data): array
    {

        $matched = preg_match('#^(?:\r?\n)*---(?:\r?\n)+(?<yaml>(?:[^\r\n]+(?:\r?\n)+)+)---(?:\r?\n(?<content>(?:.*(?:\r?\n)*)+))?$#m', (string)$data, $extracted);

        if ($matched) {

            $yaml = preg_replace('#(?:\r?\n)+#', "\n", $extracted['yaml']);
            $content = trim($extracted['content']);

            $decoded = [];

            try {

                $item_data = YamlParser::parse($yaml);

                foreach (static::$rootKeys as $key) {
                    if (array_key_exists($key, $item_data)) {
                        $decoded[$key] = $item_data[$key];
                        unset($item_data[$key]);
                    }
                }

                $snake_keys = array_map([static::class, 'snakeCase'], array_keys($item_data));
                $item_data = array_combine($snake_keys, array_values($item_data));

                $decoded['data'] = $item_data;

            } catch (\Exception $exception) {

                $decoded['data'] = [
                    'error' => $exception->getMessage(),
                ];

            }

            $decoded['data']['content'] = isset($decoded['content']) ? ($decoded['content'] . $content) : $content;

        } else {

            $decoded = [
                'data' => [
                    'error' => 'Invalid file content',
                    'content' => '',
                ],
            ];

        }

        return $decoded;
    }

    /**
     * Convert string to snake case
     *
     * @param string $value
     * @return string
     */
    protected static function snakeCase(string $value): string
    {
            $value = ucwords($value, " \t\r\n\f\v-_");
            $value = preg_replace('#[\s\-_]+#u', '', $value);
            $value = preg_replace('#(.)(?=[A-Z])#u', '$1_', $value);
            return mb_strtolower($value, 'UTF-8');
    }
}
