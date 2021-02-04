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


    public function getName()
    {
        return $this->getValueByKey($this->response, 'name');
    }

    public function getNickname()
    {
        return $this->getValueByKey($this->response, 'login');
    }


    public function getUrl()
    {
        $urlParts = array_filter([$this->domain, $this->getNickname()]);

        return count($urlParts) ? implode('/', $urlParts) : null;
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
