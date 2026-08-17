<?php
namespace Apie\Serializer\Dto;

use Apie\Core\Dto\DtoInterface;
use Apie\Serializer\Exceptions\ValidationException;
use InvalidArgumentException;

final class DurationDto implements DtoInterface
{
    public function __construct(
        public int $seconds,
        public int $nanoseconds,
        public bool $negative
    ) {
        if ($seconds < 0 || $seconds > 9223372035) {
            throw ValidationException::createFromArray([
                'seconds' => new InvalidArgumentException('Seconds should be between 0 and 9223372035')
            ]);
        }
        if ($nanoseconds < 0 || $nanoseconds > 999999999) {
            throw ValidationException::createFromArray([
                'seconds' => new InvalidArgumentException('Nanoseconds should be between 0 and 999999999, got: ' . $nanoseconds)
            ]);
        }
    }
}
