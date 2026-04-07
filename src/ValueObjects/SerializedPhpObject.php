<?php
namespace Apie\Serializer\ValueObjects;

use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\IsStringValueObject;
use function Opis\Closure\init;
use function Opis\Closure\serialize;
use function Opis\Closure\set_security_provider;
use function Opis\Closure\unserialize;

final class SerializedPhpObject implements StringValueObjectInterface
{
    use IsStringValueObject;

    /** @var array<string, mixed> */
    private static array $alreadyParsed = [];

    private static function parse(string $input): void
    {
        init(null);
        set_security_provider(null);
        self::$alreadyParsed[$input] = unserialize($input);
    }

    public static function createFromPhpObject(mixed $input): self
    {
        $inputString = serialize($input);
        self::$alreadyParsed[$inputString] = $input;
        return self::fromNative($inputString);
    }

    public static function validate(string $input): void
    {
        if (!isset(self::$alreadyParsed[$input])) {
            self::parse($input);
        }
    }

    public function toPhpObject(): mixed
    {
        self::parse($this->internal);
        return self::$alreadyParsed[$this->internal];
    }
}
