<?php
namespace Apie\Tests\Serializer\Relations;

use Apie\Serializer\Relations\EmbedRelationFromArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmbedRelationFromArrayTest extends TestCase
{
    #[Test]
    #[DataProvider('relationsProvider')]
    public function it_can_determine_to_embed_a_relation(bool $expected, array $traversal, mixed $input)
    {
        $testItem = EmbedRelationFromArray::createFromMixed($input);
        while (!empty($traversal)) {
            $key = array_shift($traversal);
            $testItem = $testItem->followField($key);
        }
        $this->assertEquals($expected, $testItem->hasEmbeddedRelation());
    }

    public static function relationsProvider(): \Generator
    {
        yield 'empty' => [false, [], []];
        yield 'exact match' => [true, ['test'], ['test']];
        yield 'wild card match' => [true, ['test'], ['*']];
        yield 'exact match subchild' => [true, ['test', 'test2'], '*.test2'];
        yield 'wild card match subchild' => [false, ['test'], '*.test'];
        yield 'subchild' => [false, ['test'], ['test.*']];
        yield 'exact match + subchild' => [true, ['test'], ['test', 'test.*']];
    }
}