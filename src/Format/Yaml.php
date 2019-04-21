<?php  namespace Ffdb\Format;

use Symfony\Component\Yaml\Yaml as YamlParser;

class Yaml implements FormatInterface
{
    /**
     * @return string
     */
    public static function getFileExtension(): string
    {
        return 'yaml';
    }

    /**
     * @param array $data
     * @return string
     */
    public static function encode(?array $data = []): string
    {
        $encoded = YamlParser::dump($data);
        return $encoded;
    }

    /**
     * @param string $data
     * @return mixed
     */
    public static function decode(?string $data): array
    {
        return (array)YamlParser::parse($data);
    }
}
