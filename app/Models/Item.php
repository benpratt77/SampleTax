<?php

namespace App\Http\Models;

class Item
{
    private $id;
    private $price;
    private $type;
    private $wrapping;

    public function __construct($data, string $type)
    {
        $this->id = $data['id'];
        $this->price = new Price($data['price']);
        $this->type = $type;
        $this->wrapping = null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $output = [];
        $output['id'] = $this->id;
        $output['price'] = $this->price->toArray();
        $output['type'] = $this->type;
        $output['wrapping'] = $this->wrapping;

        return $output;
    }
}
