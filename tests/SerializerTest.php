<?php
namespace Apie\Tests\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Enums\ColorEnum;
use Apie\Fixtures\Enums\EmptyEnum;
use Apie\Fixtures\Enums\IntEnum;
use Apie\Fixtures\Enums\NoValueEnum;
use Apie\Fixtures\Enums\RestrictedEnum;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\Serializer\Exceptions\ItemCanNotBeNormalizedInCurrentContext;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
        $this->assertEquals($expected, $serializer->denormalize($input, $desiredType, $apieContext));
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
                'password' => null,
            ]),
            $entity,
            new ApieContext()
        ];
    }

    /**
     * @test
     */
    public function empty_enums_always_fail()
    {
        $serializer = $this->givenASerializer();
        $this->expectException(ReflectionException::class);
        $serializer->denormalize(0, EmptyEnum::class, new ApieContext());
    }

    /**
     * @dataProvider invalidEnumsProvider
     * @test
     */
    public function it_can_refuse_enum_values_if_apie_context_is_missing(mixed $input, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->expectException(ItemCanNotBeNormalizedInCurrentContext::class);
        $serializer->denormalize($input, RestrictedEnum::class, $apieContext);
    }

    public function invalidEnumsProvider()
    {
        yield 'misses authenticated context' => ['green', new ApieContext()];
        // yield 'incorrect locale value' => ['red', new ApieContext(['locale' => 'gb'])]; TODO
        yield 'check 2 things should apply' => ['blue', new ApieContext(['locale' => 'gb', 'authenticated' => true])];
        yield 'any of 2 things should apply' => ['orange', new ApieContext()];
    }
}
