<?php
namespace Apie\Tests\Serializer;

use Apie\Serializer\EncoderHashmap;
use Apie\Serializer\Exceptions\NotAcceptedException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

final class EncoderHashmapTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_works_on_explicit_content_type()
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_will_throw_special_error_on_having_no_encoders()
    {
        $hashmap = new EncoderHashmap();
        $this->expectException(\LogicException::class);
        $hashmap->getAcceptedContentTypeForRequest(new Request('GET', '/'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_works_with_wildcards()
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_json_on_missing_accept_header()
    {
        $hashmap = EncoderHashmap::create();

        $request = new Request('GET', '/');

        $contentType = $hashmap->getAcceptedContentTypeForRequest($request);

        $this->assertEquals('application/json', $contentType);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_ignores_errors_on_delete_requests()
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_refuses_invalid_content_types()
    {
        $hashmap = EncoderHashmap::create();

        $request = new Request(
            'POST',
            '/',
            ['Accept' => 'application/xml']
        );
        $this->expectException(NotAcceptedException::class);
        $hashmap->getAcceptedContentTypeForRequest($request);
    }
}
