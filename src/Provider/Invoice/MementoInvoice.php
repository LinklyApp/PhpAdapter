<?php

namespace Memento\OAuth2\Client\Provider\Invoice;

use Memento\OAuth2\Client\Helpers\GenericHelpers;

class MementoInvoice
{
    /**
     * @var array
     */
    private $data = [];

    public function __construct($array)
    {
        $this->data = $array;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    private function validate($array)
    {
        $this->isRequired('reference');
    }


    private function isRequired($key)
    {
        if (!isset($this->data[$key]) || !$this->data[$key]) {
            throw new \Exception($key . ' is required');
        }
    }
}