<?php

namespace App\Http\Models;

use App\Http\Builders\TaxResponseBuilder;

class Price
{
    const AMOUNT = 'amount';
    const PRICE = 'price';

    private $amountInclusive;
    private $amountExclusive;
    private $taxRate;
    private $totalTax;
    private $salesTaxSummary;

    public function __construct(array $data)
    {
        $this->totalTax = $this->calculateTax($data[self::AMOUNT]);
        $this->amountInclusive = $data[self::AMOUNT] + $this->totalTax;
        $this->amountExclusive = $data[self::AMOUNT];
        $this->taxRate = TaxResponseBuilder::SAMPLE_TAX_RATE;
        $this->salesTaxSummary = new SalesTaxSummary($this->totalTax);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $output = [];
        $output['amount_inclusive'] = $this->amountInclusive;
        $output['amount_exclusive'] = $this->amountExclusive;
        $output['total_tax'] = $this->totalTax;
        $output['tax_rate'] = TaxResponseBuilder::SAMPLE_TAX_RATE;
        $output['sales_tax_summary'] = [$this->salesTaxSummary->toArray()];

        return $output;
    }

    /**
     * @param float $amount
     * @return float
     */
    private function calculateTax(float $amount): float
    {
        if ($amount > 0) {
            return round($amount * TaxResponseBuilder::SAMPLE_TAX_RATE, 2);
        }
        return $amount;
    }
}
