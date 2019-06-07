<?php

namespace App\Http\Models;

class TaxClass
{
    private $id;
    private $code;
    private $name;

    public function __construct($id = null, $code = null, $name = null)
    {
        $this->id = $id ?: "0";
        $this->code = $code ?: "US";
        $this->name = $name ?: "Brutal Tax";
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $output = [];
        $output['class_id'] = $this->id;
        $output['name'] = $this->name;
        $output['code'] = $this->code;

        return $output;
    }
}
