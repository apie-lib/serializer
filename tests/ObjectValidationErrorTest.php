<?php
namespace Apie\Tests\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\TestCase;

class ObjectValidationErrorTest extends TestCase
{
    public function givenASerializer(): Serializer
    {
        return Serializer::create();
    }

    /**
     * @dataProvider validationErrorProvider
     * @test
     */
    public function it_throws_validation_errors_on_incorrect_input(array $expected, mixed $input, string $desiredType, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        try {
            $serializer->denormalizeNewObject($input, $desiredType, $apieContext);
            $this->fail('denormalizeNewObject should have thrown a validation error');
        } catch (ValidationException $validationException) {
            $this->assertEquals(
                $expected,
                $validationException->getErrors()->toArray()
            );
        }
    }

    public function validationErrorProvider()
    {
        $validUuid = '123e4567-e89b-12d3-a456-426614174000';
        $validAddress = [
            'street' => 'Evergreen Terrace',
            'streetNumber' => 742,
            'zipcode' => '131313',
            'city' => 'Springfield',
        ];
        yield 'missing field' => [
            [
                'address' => "Array contains no item with index 'address'",
            ],
            [
                'id' => $validUuid,
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
        yield 'value object incorrect value' => [
            [
                'id' => 'Value "this is not a uuid" is not valid for value object of type: Uuid',
            ],
            [
                'id' => 'this is not a uuid',
                'address' => $validAddress,
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
        yield 'missing field and an incorrect field' => [
            [
                'id' => 'Value "this is not a uuid" is not valid for value object of type: Uuid',
                'address' => "Array contains no item with index 'address'",
            ],
            [
                'id' => 'this is not a uuid',
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
        yield 'value object incorrect type' => [
            [
                'id' => 'Type (object ItemHashmap) is not expected, expected Uuid',
            ],
            [
                'id' => [],
                'address' => $validAddress,
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
        yield 'composite value object incorrect type' => [
            [
                'address' => 'Type "a string" is not expected, expected array',
            ],
            [
                'id' => $validUuid,
                'address' => 'a string',
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
        yield 'composite value object missing required field' => [
            [
                'address.streetNumber' => "Array contains no item with index 'streetNumber'"
            ],
            [
                'id' => $validUuid,
                'address' => [
                    'street' => 'Evergreen Terrace',
                    'zipcode' => '131313',
                    'city' => 'Springfield',
                ]
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
    }
}
