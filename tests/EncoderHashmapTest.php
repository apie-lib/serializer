<?php
namespace Apie\Tests\Serializer;

use Apie\Serializer\EncoderHashmap;
use Apie\Serializer\Exceptions\NotAcceptedException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

final class EncoderHashmapTest extends TestCase
{
    public function testReturnsExplicitContentType()
    {
        $hashmap = EncoderHashmap::create();

        $request = new Request(
            'GET',
            '/',
            ['Accept' => 'application/json']
        );

        $contentType = $hashmap->getAcceptedContentTypeForRequest($request);

        $this->assertEquals('application/json', $contentType);
    }

    public function testReturnsWildcardContentType()
    {
        $hashmap = EncoderHashmap::create();

        $request = new Request(
            'GET',
            '/',
            ['Accept' => '*/*']
        );

        $contentType = $hashmap->getAcceptedContentTypeForRequest($request);

        $this->assertEquals('application/json', $contentType);
    }

    public function testReturnsDefaultWhenNoAcceptHeader()
    {
        $hashmap = EncoderHashmap::create();

        $request = new Request('GET', '/');

        $contentType = $hashmap->getAcceptedContentTypeForRequest($request);

        $this->assertEquals('application/json', $contentType);
    }

    public function testReturnsDefaultOnDeleteWhenNotAccepted()
    {
        $hashmap = EncoderHashmap::create();

        $request = new Request(
            'DELETE',
            '/',
            ['Accept' => 'application/xml']
        );

        $contentType = $hashmap->getAcceptedContentTypeForRequest($request);

        $this->assertEquals('application/json', $contentType);
    }

    public function testThrowsWhenNotAccepted()
    {
        $this->expectException(NotAcceptedException::class);

        $hashmap = EncoderHashmap::create();

        $request = new Request(
            'POST',
            '/',
            ['Accept' => 'application/xml']
        );

        $hashmap->getAcceptedContentTypeForRequest($request);
    }
}
