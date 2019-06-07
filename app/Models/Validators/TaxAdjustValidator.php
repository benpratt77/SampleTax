<?php

namespace App\Http\Models\Validators;

class TaxAdjustValidator
{
    /** @var TaxEstimateValidator */
    private $taxEstimateValidator;

    /**
     * TaxCommitValidator constructor.
     * @param TaxEstimateValidator $taxEstimateValidator
     */
    public function __construct(TaxEstimateValidator $taxEstimateValidator)
    {
        $this->taxEstimateValidator = $taxEstimateValidator;
    }

    /**
     * @param $requestPayload
     * @param string $id
     * @return bool
     */
    public function validateAdjustPayload($requestPayload, $id): bool
    {
        if (empty($id)) {
            return false;
        }
        // Commented out for now until we implement adjust_description has been implemented.
//        if (!isset($requestPayload['adjust_description'])) {
//            return false;
//        }
        if (!$this->taxEstimateValidator->validateEstimatePayload($requestPayload)) {
            return false;
        }

        return true;
    }
}
