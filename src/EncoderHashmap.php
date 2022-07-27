<?php
namespace Apie\Serializer;

use Apie\Core\Lists\ItemHashmap;
use Apie\Serializer\Encoders\JsonEncoder;
use Apie\Serializer\Interfaces\EncoderInterface;
use LogicException;
use NotAcceptedException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;

final class EncoderHashmap extends ItemHashmap
{
    protected bool $mutable = false;

    public function offsetGet(mixed $offset): EncoderInterface
    {
        return parent::offsetGet($offset);
    }

    public static function create(): self
    {
        return new self(['application/json' => new JsonEncoder()]);
    }

    public function getAcceptedContentTypeForRequest(RequestInterface $request): string
    {
        if (empty($this->internalArray)) {
            throw new LogicException('I am an encoder hashmap with no encoders?');
        }
        if (!$request->hasHeader('Accept')) {
            reset($this->internalArray);
            return key($this->internalArray);
        }
        $acceptHeaders = AcceptHeader::fromString($request->getHeaderLine('Accept'));
        foreach ($acceptHeaders->all() as $acceptHeaderItem) {
            $acceptHeaderValue = $acceptHeaderItem->getValue();
            if (isset($this->internalArray[$acceptHeaderValue])) {
                return $acceptHeaderValue;
            }
        }
        throw new NotAcceptedException();
    }
}
