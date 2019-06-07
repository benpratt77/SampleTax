<?php

namespace App\Http\Models\Validators;

class TaxVoidValidator
{
    /**
     * This theoretically will never be called as with no data this will never reach this endpoint.
     * @param $id
     * @return bool
     */
    public function validate($id): bool
    {
        if (empty($id)) {
            return false;
        }

        return true;
    }
}
