<?php
namespace Apie\Tests\Serializer\PropertySerializer;

use Apie\Serializer\PropertySerializer\PropertySerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class PropertySerializerTest extends TestCase
{
    #[DataProvider('provideToJsonCases')]
    #[Test]
    public function testToJson($input, $expected): void
    {
        $serializer = new PropertySerializer();
        $this->assertEquals($expected, json_decode(json_encode($serializer->toJson($input)), true));
    }

    public static function provideToJsonCases(): array
    {
        $obj = new stdClass();
        $obj->foo = 'bar';

        $anonObj = new class {
            public string $foo = 'bar';
        };

        return [
            'null' => [
                null,
                [
                    'objects' => [],
                    'root' => [ 'type' => 'null', 'value' => null ]
                ]
            ],
            'string' => [
                'string',
                [
                    'objects' => [],
                    'root' => [ 'type' => 'string', 'value' => 'string' ]
                ]
            ],
            'int' => [
                123,
                [
                    'objects' => [],
                    'root' => [ 'type' => 'int', 'value' => 123 ]
                ]
            ],
            'float' => [
                12.34,
                [
                    'objects' => [],
                    'root' => [ 'type' => 'float', 'value' => 12.34 ]
                ]
            ],
            'bool' => [
                true,
                [
                    'objects' => [],
                    'root' => [ 'type' => 'bool', 'value' => true ]
                ]
            ],
            'array' => [
                [1, 2, 3],
                [
                    'objects' => [],
                    'root' => [
                        'type' => 'array',
                        'value' => [
                            0 => [ 'type' => 'int', 'value' => 1 ],
                            1 => [ 'type' => 'int', 'value' => 2 ],
                            2 => [ 'type' => 'int', 'value' => 3 ],
                        ]
                    ]
                ]
            ],
            'stdclass' => [
                $obj,
                [
                    'objects' => [],
                    'root' => [
                        'type' => 'map',
                        'value' => [
                            'foo' => [ 'type' => 'string', 'value' => 'bar' ]
                        ]
                    ]
                ]
            ],
            'object' => [
                $anonObj,
                [
                    'objects' => [],
                    'root' => [
                        'type' => 'map',
                        'value' => [
                            'foo' => [ 'type' => 'string', 'value' => 'bar' ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
