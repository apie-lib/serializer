<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Attributes\SchemaMethod;
use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;
use Apie\Core\Lists\StringHashmap;
use Apie\TypeConverter\Exceptions\GetMultipleChainedExceptionInterface;
use Exception;
use Throwable;

#[SchemaMethod('provideSchema')]
class ValidationException extends ApieException implements HttpStatusCodeException, GetMultipleChainedExceptionInterface
{
    /**
     * @var array<int|string, Throwable>
     */
    private array $chainedExceptions = [];

    public function getStatusCode(): int
    {
        return 422;
    }

    public function __construct(private readonly StringHashmap $errors, ?Exception $previous = null)
    {
        $validationMessage = '';
        if ($errors->count() > 0) {
            $validationMessage = ':  ' . $errors->first();
        }
        $this->chainedExceptions[] = $previous;

        parent::__construct('Validation error' . $validationMessage, 0, $previous);
    }

    public function getErrors(): StringHashmap
    {
        return $this->errors;
    }

    public function getChainedExceptions(): array
    {
        return $this->chainedExceptions;
    }

    /**
     * @param array<string, Exception> $errors
     */
    public static function createFromArray(array $errors): self
    {
        $list = [];
        $previous = null;
        foreach ($errors as $property => $error) {
            $previous = $error;
            if ($error instanceof ValidationException) {
                $list = array_merge($list, $error->toArray($property));
                continue;
            }
            if ($error instanceof GetMultipleChainedExceptionInterface) {
                $list[$property] = implode(
                    PHP_EOL,
                    array_map(
                        function (Throwable $error) {
                            return $error->getMessage();
                        },
                        $error->getChainedExceptions()
                    )
                );
                continue;
            }
            $list[$property] = $error->getMessage();
        }
        $res = new ValidationException(
            new StringHashmap($list),
            $previous
        );
        $res->chainedExceptions = $errors;
        return $res;
    }

    /**
     * @return array<string, string>
     */
    private function toArray(string $prefix = ''): array
    {
        $newList = [];
        foreach ($this->errors as $property => $errorMessage) {
            $newPropertyName = self::mergeProperty($prefix, $property);
            $newList[$newPropertyName] = $errorMessage;
        }

        return $newList;
    }

    private static function mergeProperty(string $prefix, string|int $property): string
    {
        if ($prefix) {
            return ($property || $property === 0 || $property === '0') ? ($prefix . '.' . $property) : $prefix;
        }
        return $property;
    }

    public static function provideSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'message' => [
                    'type' => 'string',
                ],
                'errors' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'string',
                    ]
                ],
            ]
        ];
    }
}
