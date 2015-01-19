<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Juan Jose Toledo <juan.jose.toledo@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;
use GoIntegro\Hateoas\Http\Client;

class HttpClientUtil
{
    const CONTENT_JSON_API = 'application/vnd.api+json',
          HEADER_LOCALE = 'en-GB',
          WSSE_CREDENTIALS_PATTERN = "X-WSSE: UsernameToken Username=\"%s\", PasswordDigest=\"%s\", Nonce=\"%s\", Created=\"%s\"";

    /**
     * Obtiene un cliente de HTTP.
     * @param string $url
     * @param string $username
     * @param string $password
     * @return Client
     */
    public static function buildHttpClient(
        $url = NULL,
        $username = NULL,
        $password = NULL,
        $contentType = self::CONTENT_JSON_API,
        $language = self::HEADER_LOCALE
    )
    {
        $client = new Client($url);
        $client->setHead([
            'Accept: ' . $contentType,
            'Accept-Language: ' . $language,
            'Content-Type: application/vnd.api+json'
        ]);

        if (is_scalar($username) && is_scalar($password)) {
            $client->setOptions([CURLOPT_HTTPAUTH, CURLAUTH_ANY]);
            $client->setOption(CURLOPT_USERPWD, $username . ':' . $password);
        }

        return $client;
    }

    /**
     * Compone un header de autenticación WSSE.
     * @param string $username
     * @param string $password
     * @see http://en.wikipedia.org/wiki/WS-Security
     */
    public static function buildWsseHeader($username, $password)
    {
        $nonce = mt_rand();
        $date = new \DateTime();
        $createdAt = $date->format("c");
        $passwordDigest = base64_encode(sha1(
            base64_decode($nonce) . $createdAt . $password,
            TRUE
        ));
        $wsseCredentials = sprintf(
            static::WSSE_CREDENTIALS_PATTERN,
            $username,
            $passwordDigest,
            $nonce,
            $createdAt
        );

        return $wsseCredentials;
    }
}