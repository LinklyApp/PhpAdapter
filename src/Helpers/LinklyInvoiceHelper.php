<?php

namespace Linkly\OAuth2\Client\Helpers;

use League\OAuth2\Client\Token\AccessToken;
use Linkly\OAuth2\Client\Provider\Invoice\LinklyInvoice;
use Linkly\OAuth2\Client\Provider\LinklyProvider;

class LinklyInvoiceHelper
{
    /**
     * @var LinklyProvider
     */
    private $provider;

    public function __construct(LinklyProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getClientCredentialsAccessToken() : AccessToken
    {
        return $this->provider->getAccessToken('client_credentials', []);
    }

    public function verifyClient()
    {
        return $this->provider->verifyClient();
    }

    public function sendInvoice($invoice)
    {
        $clientCredentialsToken = $this->getClientCredentialsAccessToken();
        return $this->provider->sendInvoice($clientCredentialsToken, $invoice);
    }

}