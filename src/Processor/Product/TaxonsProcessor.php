<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Processor\Product;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class TaxonsProcessor implements TaxonsProcessorInterface
{
    public static function getDefaultPriority(): int
    {
        return 500;
    }

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RepositoryInterface $taxonRepository,
        private RepositoryInterface $productTaxonRepository,
        private FactoryInterface $productTaxonFactory,
    ) {
    }

    public function process(ProductInterface $product, array $resource): void
    {
        $product->getProductTaxons()->clear();
        $taxonCodes = array_unique($resource['categories']);

        foreach ($taxonCodes as $taxonCode) {
            $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
            if (!$taxon instanceof TaxonInterface) {
                continue;
            }
            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonRepository->findOneBy(['product' => $product, 'taxon' => $taxon]);

            if (!$productTaxon instanceof ProductTaxonInterface) {
                /** @var ProductTaxonInterface $productTaxon */
                $productTaxon = $this->productTaxonFactory->createNew();
                $productTaxon->setProduct($product);
                $productTaxon->setTaxon($taxon);
                $this->entityManager->persist($productTaxon);
            }

            $product->addProductTaxon($productTaxon);
        }
    }

    public function support(ProductInterface $product, array $resource): bool
    {
        return \array_key_exists('categories', $resource);
    }
}
