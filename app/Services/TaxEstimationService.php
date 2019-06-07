<?php

namespace App\Services;

use App\Http\Builders\TaxResponseBuilder;
use App\Http\Models\Item;
use Psr\Log\LoggerInterface;

;

class TaxEstimationService
{
    const ID_VALUE = 'sample666';
    const ITEM_SINGULAR = 'item';

    /** @var LoggerInterface */
    private $logger;
    /**  @var TaxResponseBuilder */
    private $sampleTaxLineFactory;

    /**
     * @param LoggerInterface $logger
     * @param TaxResponseBuilder $sampleTaxLineFactory
     */
    public function __construct(LoggerInterface $logger, TaxResponseBuilder $sampleTaxLineFactory)
    {
        $this->logger = $logger;
        $this->sampleTaxLineFactory = $sampleTaxLineFactory;
    }

    /**
     * @param array $requestPayload
     * @param null $externalId
     * @return array
     */
    public function getEstimate(array $requestPayload, $externalId = null): array
    {
        $result[TaxResponseBuilder::EXTERNAL_ID] = $externalId ?? self::ID_VALUE;
        $this->logger->info("{$result[TaxResponseBuilder::EXTERNAL_ID]} sent a document request");
        $result['id'] = self::ID_VALUE;
        $documents = $requestPayload[TaxResponseBuilder::DOCUMENTS];
        foreach ($documents as $document) {

            foreach ($document[TaxResponseBuilder::ITEMS] as $item) {
                $taxLine = $this->sampleTaxLineFactory->processItem($item, self::ITEM_SINGULAR);
                $result[TaxResponseBuilder::ITEMS][] = $taxLine[self::ITEM_SINGULAR];
            }
            $shipping = new Item($document[TaxResponseBuilder::SHIPPING], TaxResponseBuilder::SHIPPING);
            $handling = new Item($document[TaxResponseBuilder::HANDLING], TaxResponseBuilder::HANDLING);
            $result[TaxResponseBuilder::SHIPPING] = $shipping->toArray();
            $result[TaxResponseBuilder::HANDLING] = $handling->toArray();
        }
        return $result;
    }

    /**
     * At this stage we are just simulating a quote, in future we will add functionality to commit.
     * @param array $requestPayload
     * @return array
     */
    function commitQuote(array $requestPayload): array
    {
        return $this->getEstimate($requestPayload);
    }

    /**
     * At this stage we are just simulating a quote, in future we will add functionality to the adjust.
     * @param array $requestPayload
     * @param string $id
     * @return array
     */
    function adjustQuote(array $requestPayload, string $id): array
    {
        return $this->getEstimate($requestPayload, $id);
    }

    /**
     * Since our application does not have storage we don't need to actually do anything here.
     * @return bool
     */
    function void(): bool
    {
        return true;
    }



}