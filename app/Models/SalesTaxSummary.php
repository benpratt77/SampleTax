<?php

namespace App\Http\Models;

use App\Http\Builders\TaxResponseBuilder;

class SalesTaxSummary {

    const SUMMARY_NAME = 'Brutal Tax';
    const NAME = 'name';

    private $name;
    private $rate;
    private $amount;
    private $taxClass;

    public function __construct($tax)
    {
        $this->name = self::SUMMARY_NAME;
        $this->rate = TaxResponseBuilder::SAMPLE_TAX_RATE;
        $this->amount = $tax;
        /** @var TaxClass taxClass */
        $this->taxClass = new TaxClass();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $output = [];
        $output[self::NAME] = $this->name;
        $output['rate'] = $this->rate;
        $output['amount'] = $this->amount;
        $output['tax_class'] = $this->taxClass->toArray();
        $output['id'] = self::SUMMARY_NAME;

        return $output;
    }
}
