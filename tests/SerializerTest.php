<?php
namespace Apie\Tests\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\Enums\ColorEnum;
use Apie\Fixtures\Enums\RestrictedEnum;
use Apie\Serializer\Exceptions\ItemCanNotBeNormalizedInCurrentContext;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use UnitEnum;

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
    public function it_can_denormalizeEnums(UnitEnum $expected, mixed $input, string $desiredType, ApieContext $apieContext)
    {
        $serializer = $this->givenASerializer();
        $this->assertEquals($expected, $serializer->denormalize($input, $desiredType, $apieContext));
    }

    public function denormalizeProvider()
    {
        yield 'simple case' => [
            ColorEnum::RED,
            'red',
            ColorEnum::class,
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
