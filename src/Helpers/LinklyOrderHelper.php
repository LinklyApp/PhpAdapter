<?php

namespace Linkly\OAuth2\Client\Helpers;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Linkly\OAuth2\Client\Provider\LinklyProvider;

class LinklyOrderHelper
{
    /**
     * @var LinklyProvider
     */
    private $provider;

    public function __construct(LinklyProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getClientCredentialsAccessToken(): AccessToken
    {
        return $this->provider->getAccessToken('client_credentials', []);
    }


    /**
     * @throws IdentityProviderException
     */
    public function verifyClientCredentials()
    {
        return $this->provider->verifyClientCredentials();
    }

    /**
     * @return array
     * @throws IdentityProviderException
     */
    public function sendOrder($invoice)
    {
        $clientCredentialsToken = $this->getClientCredentialsAccessToken();
        return $this->provider->sendOrder($clientCredentialsToken, $invoice);
    }

    /**
     * @return array
     * @throws IdentityProviderException
     */
    public function sendInvoice($invoice)
    {
        $clientCredentialsToken = $this->getClientCredentialsAccessToken();
        return $this->provider->sendInvoice($clientCredentialsToken, $invoice);
    }

}