<?php


namespace Memento\OAuth2\Client\Provider\User;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class MementoUser implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $domain;
    protected $response;

    /** @var Address  */
    private $billingAddress;

    /** @var Address  */
    private $shippingAddress;

    public function __construct(array $response = array())
    {
        /** TODO Remove json_decode when claims are fixed */
        $response['billingAddress'] = json_decode($this->getValueByKey($response, 'billingAddress'), true);
        $response['shippingAddress'] = json_decode($this->getValueByKey($response, 'shippingAddress'), true);

        $this->response = $response;

        $this->billingAddress = new Address($this->getValueByKey($this->response, 'billingAddress'));
        $this->shippingAddress = new Address($this->getValueByKey($this->response, 'shippingAddress'));
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'sub');
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

    public function getFullName()
    {
        $fullName = $this->getValueByKey($this->response, 'firstName');
        $fullName .= ' ' . $this->getFamilyNameWithInfix();

        return $fullName;
    }

    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    public function isShippingAddressBillingAddress()
    {
        return $this->getValueByKey($this->response, 'shippingIsBillingAddress');
    }


    /**
     * Set resource owner domain
     *
     * @param string $domain
     *
     * @return MementoUser
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
