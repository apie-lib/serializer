<?php
namespace Apie\Tests\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Metadata\CompositeMetadata;
use Apie\Core\Metadata\MetadataFactory;
use Apie\CountryAndPhoneNumber\CountryAndPhoneNumber;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\TestHelpers\TestValidationError;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Lists\SerializedHashmap;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ObjectValidationErrorTest extends TestCase
{
    use TestValidationError;

    public function givenASerializer(): Serializer
    {
        return Serializer::create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_metadata_for_validation_errors()
    {
        $metadata = MetadataFactory::getResultMetadata(new ReflectionClass(ValidationException::class), new ApieContext());
        $this->assertInstanceOf(CompositeMetadata::class, $metadata);
        $fields = $metadata->getHashmap()->toArray();
        $this->assertArrayHasKey('message', $fields);
        $this->assertArrayHasKey('errors', $fields);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_serializes_validation_errors()
    {
        $serializer = $this->givenASerializer();
        $error = ValidationException::createFromArray(['error' => new \Exception('This is an error')]);
        $actual = $serializer->normalize($error, new ApieContext());
        $expected = new ItemHashmap([
            'message' => 'Validation error:  This is an error',
            'code' => 0,
            'errors' => new SerializedHashmap([
                'error' => 'This is an error',
            ])
        ]);
        $this->assertEquals($expected, $actual);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validationErrorProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_validation_errors_on_incorrect_input(array $expected, mixed $input, string $desiredType, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->assertValidationError(
            $expected,
            function () use ($serializer, $input, $desiredType, $apieContext) {
                $serializer->denormalizeNewObject($input, $desiredType, $apieContext);
            }
        );
    }

    public static function validationErrorProvider()
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
        if (class_exists(CountryAndPhoneNumber::class)) {
            yield 'empty country and phone number' => [
                [
                    'country' => 'Type "" is not expected, expected CountryAlpha2',
                ],
                [
                    'phoneNumber' => '',
                    'country' => '',
                ],
                CountryAndPhoneNumber::class,
                new ApieContext()
            ];
            yield 'empty phone number' => [
                [
                    'phoneNumber' => 'Phone number and country are not from the same country. Country is "NL", phone number is "(unknown)"',
                ],
                [
                    'phoneNumber' => '',
                    'country' => 'NL',
                ],
                CountryAndPhoneNumber::class,
                new ApieContext()
            ];
            yield 'empty country' => [
                [
                    'country' => 'Type "" is not expected, expected CountryAlpha2',
                ],
                [
                    'phoneNumber' => '+31611223344',
                    'country' => '',
                ],
                CountryAndPhoneNumber::class,
                new ApieContext()
            ];
        }
    }
}
