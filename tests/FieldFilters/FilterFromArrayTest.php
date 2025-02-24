<?php
namespace Apie\Tests\Serializer\FieldFilters;

use Apie\Core\Lists\StringList;
use Apie\Serializer\FieldFilters\ChainedFilter;
use Apie\Serializer\FieldFilters\FilterFromArray;
use Apie\Serializer\FieldFilters\NoFiltering;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FilterFromArrayTest extends TestCase
{
    #[Test]
    public function it_can_filter_a_column()
    {
        $testItem = FilterFromArray::createFromArray(new StringList(['test', 'pokemon', 'roblox', '*.*.id']));
        $this->assertTrue($testItem->isFiltered('test'));
        $this->assertTrue($testItem->isFiltered('test2'));
        $testItem = $testItem->followField('pokemon');
        $this->assertInstanceOf(FilterFromArray::class, $testItem);
        $testItem = $testItem->followField('digimon');
        $this->assertInstanceOf(FilterFromArray::class, $testItem);
        $this->assertTrue($testItem->isFiltered('id'));
    }

    #[Test]
    public function it_can_filter_a_descendant_column()
    {
        $testItem = FilterFromArray::createFromArray(new StringList(['test.id1', 'test.id2']));
        $this->assertTrue($testItem->isFiltered('test'));
        $testItem = $testItem->followField('test');
        $this->assertInstanceOf(FilterFromArray::class, $testItem);
        $this->assertTrue($testItem->isFiltered('id1'));
        $this->assertTrue($testItem->isFiltered('id2'));
    }

    #[Test]
    public function it_can_filter_in_between()
    {
        $testItem = FilterFromArray::createFromArray(new StringList(['test.*.id1', 'test.*.id2', 'test.test.id3']));
        $this->assertTrue($testItem->isFiltered('test'));
        $testItem = $testItem->followField('test');
        $this->assertInstanceOf(FilterFromArray::class, $testItem);
        $testItem = $testItem->followField('test');
        $this->assertInstanceOf(ChainedFilter::class, $testItem);
        $this->assertTrue($testItem->isFiltered('id1'));
        $this->assertTrue($testItem->isFiltered('id2'));
        $this->assertTrue($testItem->isFiltered('id3'));
        $testItem = $testItem->followField('test3');
        $this->assertInstanceOf(NoFiltering::class, $testItem);
    }
}
