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
                $data = array_replace($data, (array)$data['data']);
            }
            unset($data['data']);
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

                $parsed = YamlParser::parse($yaml);

                foreach (static::$rootKeys as $key) {
                    if (array_key_exists($key, $parsed)) {
                        $decoded[$key] = $parsed[$key];
                        unset($parsed[$key]);
                    }
                }

                $decoded['data'] = $parsed;

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
}
