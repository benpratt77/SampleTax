<?php

namespace App\Http\Models\Validators;

use App\Http\Builders\TaxResponseBuilder;

class TaxEstimateValidator
{
    /**
     * @param $requestPayload
     * @return bool
     */
    public function validateEstimatePayload($requestPayload): bool
    {
        $documents = $requestPayload[TaxResponseBuilder::DOCUMENTS];
        if (!$documents) {
            return false;
        }
        foreach ($documents as $document) {
            if (!isset($document['shipping']) || !isset($document['handling'])) {
                return false;
            }

            $items = $document['items'];
            if (!$items) {
                return false;
            }

            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['price'])) {
                    return false;
                }
                if (!isset($item['price']['amount']) || !isset($item['price']['tax_inclusive'])) {
                    return false;
                }
            }
        }

        return true;
    }
}
