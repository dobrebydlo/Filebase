<?php namespace Dobrebydlo\Filebase\Format;

interface FormatInterface
{
    public static function getFileExtension(): string;

    public static function encode(?array $data): string;

    public static function decode(?string $data): array;
}
