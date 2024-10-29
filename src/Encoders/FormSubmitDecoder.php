<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;

class FormSubmitDecoder implements DecoderInterface
{
    public function withOptions(string $options): DecoderInterface
    {
        return $this;
    }

    public function withParsedBody(null|array|object $parsedBody): DecoderInterface
    {
        return $this;
    }

    public function decode(string $input): string|int|float|bool|array|null
    {
        parse_str($input, $formContents);
        $data = $formContents['form'] ?? [];
        
        // a form field can submit hidden fields with <input name="_apie[typehint][fieldName]"> to provide a null or empty array
        $typehints = $formContents['_apie'] ?? [];
        if (is_string($typehints)) {
            return match ($typehints) {
                'string' => '',
                'int' => 0,
                'float' => 0.0,
                'bool' => false,
                'array' => ['_csrf' => $formContents['_csrf'] ?? 'no csrf'],
                'object' => ['_csrf' => $formContents['_csrf'] ?? 'no csrf'],
                default => null,
            };
        }
        if (is_array($typehints)) {
            foreach ($typehints as $key => $typehintData) {
                $this->fillIn($data, $key, $typehintData);
            }
        }
        $data['_csrf'] = $formContents['_csrf'] ?? 'no csrf';
        return $data;
    }

    public function requiresCsrf(): bool
    {
        return true;
    }

    private function fillIn(array& $data, string $key, string|int|float|bool|array|null $typehintData): void
    {
        if (!isset($data[$key])) {
            if (is_array($typehintData)) {
                $data[$key] = [];
            } else {
                $data[$key] = match ($typehintData) {
                    'string' => '',
                    'int' => 0,
                    'float' => 0.0,
                    'bool' => false,
                    'array' => [],
                    'object' => [],
                    default => null,
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
