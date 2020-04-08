<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Task\Product;

use Sylius\Component\Core\Model\ProductInterface;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfiguration;
use Synolia\SyliusAkeneoPlugin\Model\PipelinePayloadInterface;
use Synolia\SyliusAkeneoPlugin\Payload\Product\ProductMediaPayload;
use Synolia\SyliusAkeneoPlugin\Task\AkeneoTaskInterface;

final class InsertProductImagesTask extends AbstractInsertProductImageTask implements AkeneoTaskInterface
{
    public function __invoke(PipelinePayloadInterface $payload): PipelinePayloadInterface
    {
        try {
            if (!$payload instanceof ProductMediaPayload) {
                throw new \LogicException('Wrong payload provided.');
            }

            /** @var \Sylius\Component\Core\Model\ProductInterface $product */
            $product = $payload->getProduct();

            if (!$product instanceof ProductInterface) {
                return $payload;
            }

            $this->cleanImages($product);

            $configuration = $this->productConfigurationRepository->findOneBy([]);

            if (!$configuration instanceof ProductConfiguration) {
                return $payload;
            }

            $this->configuration = $configuration;

            $imageAttributes = $this->configuration->getAkeneoImageAttributes();
            if ($imageAttributes === null) {
                return $payload;
            }

            $this->addImage($payload, $product, $imageAttributes);
        } catch (\Throwable $throwable) {
        }

        return $payload;
    }
}
