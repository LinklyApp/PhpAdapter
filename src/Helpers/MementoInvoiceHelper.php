<?php

namespace Memento\OAuth2\Client\Helpers;

use League\OAuth2\Client\Token\AccessToken;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;
use Memento\OAuth2\Client\Provider\MementoProvider;

class MementoInvoiceHelper
{
    /**
     * @var MementoProvider
     */
    private MementoProvider $provider;
    private AccessToken $clientCredentialsToken;

    public function __construct(MementoProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getClientCredentialsAccessToken() : AccessToken
    {
        return $this->provider->getAccessToken('client_credentials', []);
    }

    public function sendInvoice(MementoInvoice $invoice)
    {
        $this->clientCredentialsToken = $this->getClientCredentialsAccessToken();
        return $this->provider->sendInvoice($invoice, $this->clientCredentialsToken);
    }
}