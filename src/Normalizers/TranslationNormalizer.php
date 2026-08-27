<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\ContextConstants;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\Lists\TranslationStringSet;
use Apie\Core\Translator\ValueObjects\AbstractTranslation;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;

class TranslationNormalizer implements NormalizerInterface
{
    public function supportsNormalization(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return $object instanceof AbstractTranslation || $object instanceof TranslationStringSet;
    }
    public function normalize(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): string {
        $context = $apieSerializerContext->getContext();
        if ($context->hasContext(ContextConstants::ACCEPT_LOCALE)) {
            $context = $context->withContext(
                ContextConstants::LOCALE,
                $context->getContext(ContextConstants::ACCEPT_LOCALE)
            );
        }
        $translator = $context->getContext(ApieTranslatorInterface::class, false);

        if ($translator instanceof ApieTranslatorInterface) {
            return $translator->getGeneralTranslation($context, $object) ?? Utils::toString($object);
        }

        return Utils::toString($object);
    }
}
