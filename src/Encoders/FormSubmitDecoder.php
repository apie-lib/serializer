<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;
use stdClass;
use UnexpectedValueException;

class FormSubmitDecoder implements DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null
    {
        parse_str($input, $formContents);
        $data = $formContents['form'] ?? [];
        $data['_csrf'] = $formContents['_csrf'] ?? 'no csrf';
        // TODO internal data to add missing fields that are not compatible
        foreach ($formContents['_apie']['typehint'] ?? [] as $key => $typehintData) {
            $this->fillIn($data, $key, $typehintData);
        }

        return $data;
    }

    private function fillIn(array& $data, string $key, string|int|float|bool|array|null $typehintData): void
    {
        if (!isset($data[$key])) {
            if (is_array($typehintData)) {
                $data[$key] = [];
            } else {
                $data[$key] = match($typehintData) {
                    'string' => '',
                    'int' => 0,
                    'float' => 0.0,
                    'bool' => false,
                    'null' => null,
                    'array' => [],
                    'object' => [],
                    default => throw new UnexpectedValueException('Expected string|int|float|bool|null|array|object, got "' . $typehintData . '"')
                };
                return;
            }
        }
        if (is_array($typehintData)) {
            foreach ($typehintData as $subKey => $typehintSubdata) {
                $this->fillIn($data[$key], $subKey, $typehintSubdata);
            }
        }
    }
}
