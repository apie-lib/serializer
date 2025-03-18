<?php
namespace Apie\Tests\Serializer\Encoders;

use Apie\Serializer\Encoders\FormSubmitDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormSubmitDecoderTest extends TestCase
{
    #[Test]
    #[DataProvider('decodeProvider')]
    public function it_can_decode_a_form_submit(mixed $expected, string|array $input)
    {
        $testItem = new FormSubmitDecoder();
        $actual = $testItem->decode(is_string($input) ? $input : http_build_query($input));
        $this->assertEquals($expected, $actual);
    }

    public static function decodeProvider(): \Generator
    {
        yield 'empty' => [['_csrf' => 'no csrf'], []];
        yield 'normal form submit' => [
            ['a' => '1', '_csrf' => 'no csrf'],
            ['form' => ['a' => 1]]
        ];
        yield 'empty array' => [
            ['_csrf' => 'no csrf'],
            ['_apie' => 'array'],
        ];
        yield 'empty object' => [
            ['_csrf' => 'no csrf'],
            ['_apie' => 'object'],
        ];
        yield 'empty string' => [
            '',
            ['_apie' => 'string'],
        ];
        yield 'empty int' => [
            0,
            ['_apie' => 'int'],
        ];
        yield 'empty float' => [
            0,
            ['_apie' => 'float'],
        ];
        yield 'empty bool' => [
            false,
            ['_apie' => 'bool'],
        ];
        yield 'true' => [
            true,
            ['_apie' => 'true'],
        ];
        yield 'false' => [
            false,
            ['_apie' => 'false'],
        ];
        yield 'unknown typehint' => [
            null,
            ['_apie' => 'unknown'],
        ];
        yield 'very complex' => [
            [
                '_csrf' => '123456',
                'test' => [
                    'test' => '',
                    'test2' => '12',
                    'test3' => false,
                    'test4' => [],
                    'test5' => [],
                    'test6' => 0,
                    'test7' => 0,
                    'test8' => true,
                    'test9' => false,
                    'test10' => null,
                ]
            ],
            [
                '_csrf' => '123456',
                'form' => [
                    'test' => [
                        'test2' => '12',
                    ]
                ],
                '_apie' => [
                    'test' => [
                        'test' => 'string',
                        'test3' => 'bool',
                        'test4' => 'array',
                        'test5' => 'object',
                        'test6' => 'int',
                        'test7' => 'float',
                        'test8' => 'true',
                        'test9' => 'false',
                        'test10' => 'unknown',
                    ]
                ]
            ]
        ];
    }
}