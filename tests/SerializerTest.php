<?php
namespace Apie\Tests\Serializer;

use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Repositories\Lists\PaginatedResult;
use Apie\Core\Repositories\Search\QuerySearch;
use Apie\Core\Repositories\ValueObjects\LazyLoadedListIdentifier;
use Apie\Fixtures\Dto\DefaultExampleDto;
use Apie\Fixtures\Dto\ExampleDto;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Enums\ColorEnum;
use Apie\Fixtures\Enums\EmptyEnum;
use Apie\Fixtures\Enums\Gender;
use Apie\Fixtures\Enums\IntEnum;
use Apie\Fixtures\Enums\NoValueEnum;
use Apie\Fixtures\Enums\RestrictedEnum;
use Apie\Fixtures\Identifiers\UserWithAddressIdentifier;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\Serializer\Exceptions\ItemCanNotBeNormalizedInCurrentContext;
use Apie\Serializer\Lists\SerializedList;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SerializerTest extends TestCase
{
    public function givenASerializer(): Serializer
    {
        return Serializer::create();
    }

    /**
     * @dataProvider denormalizeProvider
     * @test
     */
    public function it_can_denormalize_objects(object $expected, mixed $input, string $desiredType, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->assertEquals($expected, $serializer->denormalizeNewObject($input, $desiredType, $apieContext));
    }

    public function denormalizeProvider()
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
        $dto->gender = Gender::MALE;
        yield 'Simple DTO, no default arguments' => [
            $dto,
            [
                'string' => 'string',
                'integer' => 12,
                'floatingPoint' => -42.5,
                'trueOrFalse' => true,
                'mixed' => 48,
                'gender' => 'M',
            ],
            ExampleDto::class,
            new ApieContext()
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_can_normalize_objects(string|int|ItemHashmap $expected, object $input, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $actual = $serializer->normalize($input, $apieContext);
        $this->assertEquals($expected, $actual);
    }

    public function normalizeProvider()
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
        yield 'Pagination result' => [
            new ItemHashmap([
                'totalCount' => 1,
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
                1,
                new ItemList([$entity]),
                0,
                20,
                new QuerySearch(0, 20, 'search')
            ),
            new ApieContext()
        ];
    }

    /**
     * @test
     */
    public function empty_enums_always_fail()
    {
        $serializer = $this->givenASerializer();
        $this->expectException(InvalidTypeException::class);
        $serializer->denormalizeNewObject(null, EmptyEnum::class, new ApieContext());
    }

    /**
     * @dataProvider invalidEnumsProvider
     * @test
     */
    public function it_can_refuse_enum_values_if_apie_context_is_missing(mixed $input, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->expectException(ItemCanNotBeNormalizedInCurrentContext::class);
        $serializer->denormalizeNewObject($input, RestrictedEnum::class, $apieContext);
    }

    public function invalidEnumsProvider()
    {
        yield 'misses authenticated context' => ['green', new ApieContext()];
        // yield 'incorrect locale value' => ['red', new ApieContext(['locale' => 'gb'])]; TODO
        yield 'check 2 things should apply' => ['blue', new ApieContext(['locale' => 'gb', 'authenticated' => true])];
        yield 'any of 2 things should apply' => ['orange', new ApieContext()];
    }
}
