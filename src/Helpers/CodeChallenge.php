<?php

namespace League\OAuth2\Client\Helpers;

class CodeChallenge
{

    public $verifier;
    public $challenge;
    public $challengeMethod = 'S256';

    public function generate()
    {
        $this->generateVerifier();
        $this->generateChallenge();
    }

    private function generateVerifier()
    {
//        $random = bin2hex(openssl_random_pseudo_bytes(32));
        $random = 'ec6ab82eab0a26c33f9d286211e406b88cd3402e005ac2d1e06e8a17a84d9f31';
        $this->verifier = $this->base64url_encode(pack('H*', $random));
    }

    private function generateChallenge()
    {
        $this->challenge = $this->base64url_encode(pack('H*', hash('sha256', $this->verifier)));
    }

    private function base64url_encode($plainText)
    {
        $base64 = base64_encode($plainText);
        $base64 = trim($base64, "=");
        $base64url = strtr($base64, '+/', '-_');
        return ($base64url);
    }

}




