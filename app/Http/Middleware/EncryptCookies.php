<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;
use Symfony\Component\HttpFoundation\Response;

class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];

    protected function safeDecrypt($cookie)
    {
        return \json_decode(
            $this->encrypter->decrypt($cookie, false)
        );
    }

    protected function safeEncrypt($cookie)
    {
        return $this->encrypter->encrypt(\json_encode($cookie), false);
    }

    /**
     * Encrypt the cookies on an outgoing response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function encrypt(Response $response)
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($this->isDisabled($cookie->getName())) {
                continue;
            }
            $response->headers->setCookie($this->duplicate(
                $cookie, $this->safeEncrypt($cookie->getValue())
            ));
        }
        return $response;
    }

    /**
     * Decrypt the given cookie and return the value.
     *
     * @param  string|array  $cookie
     * @return string|array
     */
    protected function decryptCookie($cookie)
    {
        return is_array($cookie)
            ? $this->decryptArray($cookie)
            : $this->safeDecrypt($cookie);
    }

    /**
     * Decrypt an array based cookie.
     *
     * @param  array  $cookie
     * @return array
     */
    protected function decryptArray(array $cookie)
    {
        $decrypted = [];
        foreach ($cookie as $key => $value) {
            if (is_string($value)) {
                $decrypted[$key] = $this->safeDecrypt($value);
            }
        }
        return $decrypted;
    }

}
