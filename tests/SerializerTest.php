<?php
namespace Apie\Tests\Serializer;

use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\Lists\PaginatedResult;
use Apie\Core\Datalayers\Search\QuerySearch;
use Apie\Core\Datalayers\ValueObjects\LazyLoadedListIdentifier;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Permissions\PermissionInterface;
use Apie\Core\Permissions\SerializedPermission;
use Apie\Fixtures\Dto\DefaultExampleDto;
use Apie\Fixtures\Dto\DtoWithPromotedProperties;
use Apie\Fixtures\Dto\ExampleDto;
use Apie\Fixtures\Entities\CollectionItemOwned;
use Apie\Fixtures\Entities\Order;
use Apie\Fixtures\Entities\Polymorphic\AnimalIdentifier;
use Apie\Fixtures\Entities\Polymorphic\Cow;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Enums\ColorEnum;
use Apie\Fixtures\Enums\EmptyEnum;
use Apie\Fixtures\Enums\Gender;
use Apie\Fixtures\Enums\IntEnum;
use Apie\Fixtures\Enums\NoValueEnum;
use Apie\Fixtures\Enums\RestrictedEnum;
use Apie\Fixtures\FuturePhpVersion;
use Apie\Fixtures\Identifiers\CollectionItemOwnedIdentifier;
use Apie\Fixtures\Identifiers\OrderIdentifier;
use Apie\Fixtures\Identifiers\UserWithAddressIdentifier;
use Apie\Fixtures\Lists\OrderLineList;
use Apie\Fixtures\Php84\AsyncVisibility;
use Apie\Fixtures\Php84\PropertyHooks;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\Serializer\Exceptions\ItemCanNotBeNormalizedInCurrentContext;
use Apie\Serializer\Lists\SerializedHashmap;
use Apie\Serializer\Lists\SerializedList;
use Apie\Serializer\Serializer;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophet;
use ReflectionClass;

class SerializerTest extends TestCase
{
    use ProphecyTrait;

    public function givenASerializer(): Serializer
    {
        return Serializer::create();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('denormalizeProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_denormalize_objects(object $expected, mixed $input, string $desiredType, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->assertEquals($expected, $serializer->denormalizeNewObject($input, $desiredType, $apieContext));
    }

    public static function denormalizeProvider()
    {
        yield 'string enum' => [
            ColorEnum::RED,
            'red',
            ColorEnum::class,
            new ApieContext()
        ];
        yield 'integer enum' => [
            IntEnum::RED,
            0,
            IntEnum::class,
            new ApieContext()
        ];
        yield 'enum without values' => [
            NoValueEnum::RED,
            'RED',
            NoValueEnum::class,
            new ApieContext()
        ];
        yield 'restriction applied' => [
            RestrictedEnum::BLUE,
            'blue',
            RestrictedEnum::class,
            new ApieContext(['authenticated' => true, 'locale' => 'nl'])
        ];
        yield 'DateTime interface' => [
            new DateTimeImmutable('2005-08-15T15:52:01+00:00'),
            '2005-08-15T15:52:01+00:00',
            DateTimeInterface::class,
            new ApieContext(),
        ];
        yield 'DateTime object' => [
            new DateTime('2005-08-15T15:52:01+00:00'),
            '2005-08-15T15:52:01+00:00',
            DateTime::class,
            new ApieContext(),
        ];
        yield 'DateTimeZone object' => [
            new DateTimeZone('Europe/London'),
            'Europe/London',
            DateTimeZone::class,
            new ApieContext(),
        ];
        $entity = new UserWithAddress(
            AddressWithZipcodeCheck::fromNative([
                'street' => 'Evergreen Terrace',
                'streetNumber' => 742,
                'zipcode' => '131313',
                'city' => 'Springfield',
            ]),
            new UserWithAddressIdentifier('123e4567-e89b-12d3-a456-426614174000')
        );
        yield 'entity' => [
            $entity,
            [
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'address' => [
                    'street' => 'Evergreen Terrace',
                    'streetNumber' => 742,
                    'zipcode' => '131313',
                    'city' => 'Springfield',
                ],
            ],
            UserWithAddress::class,
            new ApieContext()
        ];
        yield 'Simple DTO' => [
            new DefaultExampleDto(),
            [
                'string' => 'default value',
                'integer' => 42,
                'floatingPoint' => 1.5,
                'trueOrFalse' => true,
                'mixed' => 48,
                'noType' => 'Boom',
                'gender' => 'M',
            ],
            DefaultExampleDto::class,
            new ApieContext()
        ];
        yield 'Simple DTO, all default' => [
            new DefaultExampleDto(),
            [],
            DefaultExampleDto::class,
            new ApieContext()
        ];
        $dto = new ExampleDto();
        $dto->string = 'string';
        $dto->integer = 12;
        $dto->floatingPoint = -42.5;
        $dto->trueOrFalse = true;
        $dto->mixed = 48;
        $dto->noType = new ItemList([1, 2, 3]);
        $dto->gender = Gender::MALE;
        yield 'Simple DTO, no default arguments' => [
            $dto,
            [
                'string' => 'string',
                'integer' => 12,
                'floatingPoint' => -42.5,
                'trueOrFalse' => true,
                'mixed' => 48,
                'noType' => [1, 2, 3],
                'gender' => 'M',
            ],
            ExampleDto::class,
            new ApieContext()
        ];
        yield 'DTO with promoted properties and default value' => [
            new DtoWithPromotedProperties(
                null,
                null
            ),
            [],
            DtoWithPromotedProperties::class,
            new ApieContext()
        ];

        $id = AnimalIdentifier::createRandom();
        yield 'Polymorphic entity, specific' => [
            new Cow($id),
            [
                'animalType' => 'cow',
                'id' => $id->toNative(),
            ],
            Cow::class,
            new ApieContext()
        ];

        $orderIdentifier = OrderIdentifier::createRandom();
        $order = new Order($orderIdentifier, new OrderLineList([]));
        $prophet = new Prophet();
        $dataLayer = $prophet->prophesize(ApieDatalayer::class);
        $dataLayer->find($orderIdentifier)->willReturn($order);

        yield 'Identifier with data layer check' => [
            $orderIdentifier,
            $orderIdentifier->toNative(),
            OrderIdentifier::class,
            new ApieContext([
                ApieDatalayer::class => $dataLayer,
            ])
        ];

        yield 'Object alias' => [
            new SerializedPermission('test'),
            'test',
            PermissionInterface::class,
            new ApieContext()
        ];

        if (PHP_VERSION_ID >= 80400) {
            FuturePhpVersion::loadPhp84Classes();
            yield 'PHP 8.4 object with async visibility' => [
                new AsyncVisibility('test', 'option'),
                [
                    'name' => 'test',
                    'option' => 'option',
                ],
                AsyncVisibility::class,
                new ApieContext(),
            ];
            yield 'PHP 8.4 object with property hooks' => [
                new PropertyHooks('test'),
                [
                    'name' => 'test',
                ],
                PropertyHooks::class,
                new ApieContext(),
            ];
            yield 'PHP 8.4 object with property hooks virtual property' => [
                new PropertyHooks('Override'),
                [
                    'name' => 'test',
                    'virtualSetter' => 'override',
                ],
                PropertyHooks::class,
                new ApieContext(),
            ];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('normalizeProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_normalize_objects(string|int|ItemHashmap|ItemList $expected, object $input, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $actual = $serializer->normalize($input, $apieContext);
        $this->assertEquals($expected, $actual);
    }

    public static function normalizeProvider()
    {
        yield 'string enum' => [
            'red',
            ColorEnum::RED,
            new ApieContext(),
        ];
        yield 'integer enum' => [
            0,
            IntEnum::RED,
            new ApieContext(),
        ];
        yield 'backed enum' => [
            'RED',
            NoValueEnum::RED,
            new ApieContext(),
        ];
        $entity = new UserWithAddress(AddressWithZipcodeCheck::fromNative([
            'street' => 'Evergreen Terrace',
            'streetNumber' => 742,
            'zipcode' => '131313',
            'city' => 'Springfield',
        ]));
        yield 'Simple entity' => [
            new ItemHashmap([
                'id' => $entity->getId()->toNative(),
                'address' => new ItemHashmap([
                    'street' => 'Evergreen Terrace',
                    'streetNumber' => 742,
                    'zipcode' => '131313',
                    'city' => 'Springfield',
                ]),
            ]),
            $entity,
            new ApieContext()
        ];
        $objectId = CollectionItemOwnedIdentifier::createRandom();
        yield 'Entity with context' => [
            new ItemHashmap([
                'id' => $objectId->toNative(),
                'owned' => true,
            ]),
            new CollectionItemOwned($objectId, $entity, true),
            new ApieContext()
        ];
        yield 'Simple DTO' => [
            new ItemHashmap([
                'string' => 'default value',
                'integer' => 42,
                'floatingPoint' => 1.5,
                'trueOrFalse' => true,
                'mixed' => 48,
                'noType' => 'Boom',
                'gender' => 'M',
            ]),
            new DefaultExampleDto(),
            new ApieContext()
        ];
        $animalId = AnimalIdentifier::createRandom();
        yield 'Polymorphic entity' => [
            new ItemHashmap([
                'animalType' => 'cow',
                'hasMilk' => false,
                'id' => $animalId->toNative(),
            ]),
            new Cow($animalId),
            new ApieContext()
        ];
        yield 'Generic hashmap' => [
            new SerializedHashmap(['a' => 2, 'b' => 'pizza']),
            new ItemHashmap(['a' => 2, 'b' => 'pizza']),
            new ApieContext(),
        ];
        yield 'Generic list' => [
            new SerializedList([2, 'pizza']),
            new ItemList([2, 'pizza']),
            new ApieContext(),
        ];
        yield 'Pagination result' => [
            new ItemHashmap([
                'totalCount' => 2,
                'filteredCount' => 1,
                'list' => new SerializedList(
                    [
                        new ItemHashmap([
                            'id' => $entity->getId()->toNative(),
                            'address' => new ItemHashmap([
                                'street' => 'Evergreen Terrace',
                                'streetNumber' => 742,
                                'zipcode' => '131313',
                                'city' => 'Springfield',
                            ]),
                        ]),
                    ]
                ),
                'first' => '/default/UserWithAddress?search=search',
                'last' => '/default/UserWithAddress?search=search',
            ]),
            new PaginatedResult(
                LazyLoadedListIdentifier::createFrom(new BoundedContextId('default'), new ReflectionClass($entity)),
                2,
                1,
                new ItemList([$entity]),
                0,
                20,
                new QuerySearch(0, 20, 'search')
            ),
            new ApieContext()
        ];

        if (PHP_VERSION_ID >= 80400) {
            FuturePhpVersion::loadPhp84Classes();
            yield 'PHP 8.4 object with async visibility' => [
                new ItemHashmap([
                    'name' => 'test',
                    'option' => 'option',
                ]),
                new AsyncVisibility('test', 'option'),
                new ApieContext(),
            ];
            yield 'PHP 8.4 object with property hooks' => [
                new ItemHashmap(['name' => 'test', 'virtual' => 'This is an example']),
                new PropertyHooks('test'),
                new ApieContext(),
            ];
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function empty_enums_always_fail()
    {
        $serializer = $this->givenASerializer();
        $this->expectException(InvalidTypeException::class);
        $serializer->denormalizeNewObject(null, EmptyEnum::class, new ApieContext());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidEnumsProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_refuse_enum_values_if_apie_context_is_missing(mixed $input, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->expectException(ItemCanNotBeNormalizedInCurrentContext::class);
        $serializer->denormalizeNewObject($input, RestrictedEnum::class, $apieContext);
    }

    public static function invalidEnumsProvider()
    {
        yield 'misses authenticated context' => ['green', new ApieContext()];
        // yield 'incorrect locale value' => ['red', new ApieContext(['locale' => 'gb'])]; TODO
        yield 'check 2 things should apply' => ['blue', new ApieContext(['locale' => 'gb', 'authenticated' => true])];
        yield 'any of 2 things should apply' => ['orange', new ApieContext()];
    }
}
