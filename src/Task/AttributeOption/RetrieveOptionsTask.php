<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Task\AttributeOption;

use Psr\Log\LoggerInterface;
use Synolia\SyliusAkeneoPlugin\Exceptions\UnsupportedAttributeTypeException;
use Synolia\SyliusAkeneoPlugin\Logger\Messages;
use Synolia\SyliusAkeneoPlugin\Model\PipelinePayloadInterface;
use Synolia\SyliusAkeneoPlugin\Payload\Option\OptionsPayload;
use Synolia\SyliusAkeneoPlugin\Task\AkeneoTaskInterface;
use Synolia\SyliusAkeneoPlugin\TypeMatcher\Attribute\AttributeTypeMatcher;
use Synolia\SyliusAkeneoPlugin\TypeMatcher\Attribute\SelectAttributeTypeMatcher;

final class RetrieveOptionsTask implements AkeneoTaskInterface
{
    /** @var \Synolia\SyliusAkeneoPlugin\TypeMatcher\Attribute\AttributeTypeMatcher */
    private $attributeTypeMatcher;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $type;

    public function __construct(
        AttributeTypeMatcher $attributeTypeMatcher,
        LoggerInterface $logger
    ) {
        $this->attributeTypeMatcher = $attributeTypeMatcher;
        $this->logger = $logger;
    }

    /**
     * @param OptionsPayload $payload
     */
    public function __invoke(PipelinePayloadInterface $payload): PipelinePayloadInterface
    {
        $this->logger->debug(self::class);
        $this->type = 'Attribute Option';
        $this->logger->notice(Messages::retrieveFromAPI($this->type));

        $compatibleAttributes = [];
        foreach ($payload->getResources() as $resource) {
            try {
                $attributeTypeMatcher = $this->attributeTypeMatcher->match($resource['type']);
                if (!$attributeTypeMatcher instanceof SelectAttributeTypeMatcher) {
                    continue;
                }
                $compatibleAttributes[$resource['code']] = ['isMultiple' => $attributeTypeMatcher->isMultiple($resource['type'])];
            } catch (UnsupportedAttributeTypeException $unsuportedAttributeTypeException) {
                $this->logger->warning($unsuportedAttributeTypeException->getMessage());

                continue;
            }
        }

        $optionsPayload = $this->process($payload, $compatibleAttributes);
        $this->logger->info(Messages::totalToImport($this->type, count($optionsPayload->getResources())));

        return $optionsPayload;
    }

    /**
     * @param OptionsPayload $payload
     */
    private function process(PipelinePayloadInterface $payload, array $attributeCodes): OptionsPayload
    {
        $optionsPayload = new OptionsPayload($payload->getAkeneoPimClient());
        $resources = [];
        foreach ($attributeCodes as $attributeCode => $values) {
            $resources[$attributeCode] = [
                'isMultiple' => $values['isMultiple'],
                'resources' => $payload->getAkeneoPimClient()->getAttributeOptionApi()->all($attributeCode),
            ];
        }
        $optionsPayload->setResources($resources);

        return $optionsPayload;
    }
}
