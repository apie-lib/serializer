<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Attributes\SchemaMethod;
use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;
use Apie\Core\Lists\StringHashmap;
use Exception;

#[SchemaMethod('provideSchema')]
class ValidationException extends ApieException implements HttpStatusCodeException
{
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

        parent::__construct('Validation error' . $validationMessage, 0, $previous);
    }

    public function getErrors(): StringHashmap
    {
        return $this->errors;
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
            $list[$property] = $error->getMessage();
        }

        return new ValidationException(
            new StringHashmap($list),
            $previous
        );
    }

    /**
     * @return array<string, string>
     */
    private function toArray(string $prefix = ''): array
    {
        $newList = [];
        foreach ($this->errors as $property => $errorMessage) {
            $newPropertyName = $prefix ? ($prefix . '.' . $property) : $property;
            $newList[$newPropertyName] = $errorMessage;
        }

        return $newList;
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
