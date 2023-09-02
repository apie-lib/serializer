<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Datalayers\Lists\PaginatedResult;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\ServerRequestInterface;

class PaginatedResultNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof PaginatedResult;
    }

    /**
     * @param PaginatedResult<EntityInterface> $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): ItemHashmap
    {
        $context = $apieSerializerContext->getContext();
        $uri = $object->id->asUrl();
        if ($context->hasContext(ServerRequestInterface::class)) {
            // TODO extract URI from request.
        }
        return new ItemHashmap(array_filter(
            [
                'totalCount' => $object->totalCount,
                'list' => $apieSerializerContext->normalizeAgain($object->list),
                'first' => $this->renderFirst($uri, $object),
                'last' => $this->renderLast($uri, $object),
                'prev' => $this->renderPrev($uri, $object),
                'next' => $this->renderNext($uri, $object),
            ],
            function (mixed $value) {
                return $value !== null;
            }
        ));
    }

    /**
     * @param PaginatedResult<EntityInterface> $object
     */
    private function renderFirst(string $uri, PaginatedResult $object): string
    {
        return $uri . $object->querySearch->withPageIndex(0)->toHttpQuery();
    }

    /**
     * @param PaginatedResult<EntityInterface> $object
     */
    private function renderLast(string $uri, PaginatedResult $object): string
    {
        $pageIndex = 1 + floor($object->totalCount / $object->pageSize);
        if ($pageIndex * $object->pageSize > $object->totalCount) {
            $pageIndex--;
        }
        return $uri . $object->querySearch->withPageIndex((int) $pageIndex)->toHttpQuery();
    }

    /**
     * @param PaginatedResult<EntityInterface> $object
     */
    private function renderPrev(string $uri, PaginatedResult $object): ?string
    {
        if ($object->pageNumber > 0) {
            return $object->querySearch->withPageIndex($object->pageNumber - 1)->toHttpQuery();
        }

        return null;
    }

    /**
     * @param PaginatedResult<EntityInterface> $object
     */
    private function renderNext(string $uri, PaginatedResult $object): ?string
    {
        $pageIndex = $object->pageNumber + 1;
        if ($pageIndex * $object->pageSize > $object->totalCount) {
            return null;
        }
        return $uri . $object->querySearch->withPageIndex((int) $pageIndex)->toHttpQuery();
    }
}
