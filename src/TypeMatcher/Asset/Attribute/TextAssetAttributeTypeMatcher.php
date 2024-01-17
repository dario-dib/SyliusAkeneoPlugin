<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\TypeMatcher\Asset\Attribute;

use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Synolia\SyliusAkeneoPlugin\Builder\Asset\Attribute\TextAssetAttributeValueBuilder;

final class TextAssetAttributeTypeMatcher implements AssetAttributeTypeMatcherInterface
{
    private const SUPPORTED_TYPE = 'text';

    public function getType(): string
    {
        return TextAttributeType::TYPE;
    }

    public function support(string $akeneoType): bool
    {
        return self::SUPPORTED_TYPE === $akeneoType;
    }

    public function getBuilder(): string
    {
        return TextAssetAttributeValueBuilder::class;
    }

    public function getTypeClassName(): string
    {
        return TextAttributeType::class;
    }
}
