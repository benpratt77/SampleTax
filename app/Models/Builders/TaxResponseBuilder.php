<?php

namespace App\Http\Builders;

use App\Http\Models\Item;

class TaxResponseBuilder
{
    const SAMPLE_TAX_RATE = 0.5;
    const DOCUMENTS = 'documents';
    const ITEMS = 'items';
    const SHIPPING = 'shipping';
    const HANDLING = 'handling';
    const EXTERNAL_ID = 'external_id';

    /**
     * Processes an item from the document form submission.
     * This can either be a purchased Item, Shipping or Handling
     *
     * @param array $data
     * @param string $type
     * @return array
     */
    public function processItem(array $data, string $type): array
    {
        $item = new Item($data, $type);
        $taxDetails[$type] = $item->toArray();
        return $taxDetails;
    }
}