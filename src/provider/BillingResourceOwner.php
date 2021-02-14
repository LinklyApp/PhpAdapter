<?php


namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class BillingResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $domain;
    protected $response;

    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'firstName');
    }

    public function getFamilyNameInfix()
    {
        return $this->getValueByKey($this->response, 'familyNameInfix');
    }

    public function getFamilyName()
    {
        return $this->getValueByKey($this->response, 'familyName');
    }

    public function getFamilyNameWithInfix()
    {
        $infix = '';

        if ($this->getValueByKey($this->response, 'familyNameInfix')) {
            $infix .= $this->getValueByKey($this->response, 'familyNameInfix') . ' ';
        }

        return $infix . $this->getValueByKey($this->response, 'familyName');
    }

    public function getBillingAddress()
    {
        return $this->getValueByKey($this->response, 'billing');
    }

    public function isShippingAddressBillingAddress()
    {
        return $this->getValueByKey($this->response, 'shippingIsBillingAddress');
    }

    public function getShippingAddress()
    {
        return $this->getValueByKey($this->response, 'shipping');
    }

    public function getFullName()
    {
        $fullName = $this->getValueByKey($this->response, 'firstName');
        $fullName .= ' ' . $this->getFamilyNameWithInfix();

        return $fullName;
    }

    /**
     * Set resource owner domain
     *
     * @param  string $domain
     *
     * @return ResourceOwner
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
