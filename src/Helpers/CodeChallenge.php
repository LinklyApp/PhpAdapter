<?php

namespace Linkly\OAuth2\Client\Helpers;

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
        $random = bin2hex(openssl_random_pseudo_bytes(32));
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




