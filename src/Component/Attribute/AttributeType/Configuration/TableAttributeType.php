<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Component\Attribute\AttributeType\Configuration;

use Symfony\Component\Form\AbstractType;

final class TableAttributeType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'sylius_attribute_type_configuration_table';
    }
}
