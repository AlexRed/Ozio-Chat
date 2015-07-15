<?php defined('_JEXEC') or die('Restricted access');
/*
The MIT License (MIT)

Copyright (c) 2013 Ricardo Pereira

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * TwitterOAuth - https://github.com/ricardoper/TwitterOAuth
 * PHP library to communicate with Twitter OAuth API version 1.1
 *
 * @author Ricardo Pereira <github@ricardopereira.es>
 * @copyright 2014
 */

namespace TwitterOAuth\Auth;

class ApplicationOnlyAuth extends AuthAbstract
{
    /**
     * Expects the follow parameters:
     *   - consumer_key      Twitter API key      * Required
     *   - consumer_secret   Twitter API secret   * Required
     */
    protected $requiredCredentials = array(
        'consumer_key',
        'consumer_secret',
    );

    protected $urls = array(
        'domain' => 'https://api.twitter.com/',
        'api' => '1.1/',
        'getToken' => 'oauth2/token',
        'invToken' => 'oauth2/invalidate_token',
    );

    protected $bearerToken = null;


    /**
     *  Get a bearer token from consumer keys
     *
     * @return null|string  Bearer token
     * @throws \TwitterOAuth\Exception\CurlException
     * @throws \TwitterOAuth\Exception\TwitterException
     */
    public function getBearerToken()
    {
        $url = $this->getBearerTokenUrl();

        $params = array(
            'post' => array('grant_type' => 'client_credentials'),
            'headers' => $this->buildBearerTokenHeader(),
        );

        $response = $this->curl->send($url, $params);

        $obj = json_decode($response['body']);

        if (!$obj || !isset($obj->token_type) || $obj->token_type != 'bearer') {
            $this->findExceptions($response);
        }

        $this->bearerToken = rawurldecode($obj->access_token);

        unset($url, $params, $response, $obj);

        return $this->bearerToken;
    }

    /**
     *  Set a bearer token
     *
     * @param string $bearerToken Bearer Token
     * @return bool
     */
    public function setBearerToken($bearerToken)
    {
        $this->bearerToken = $bearerToken;

        unset($bearerToken);

        return true;
    }

    /**
     *  Invalidate a bearer token
     *
     * @return bool
     * @throws \TwitterOAuth\Exception\CurlException
     * @throws \TwitterOAuth\Exception\TwitterException
     */
    public function invalidateBearerToken()
    {
        $url = $this->getInvalidateBearerTokenUrl();

        $bearerToken = $this->bearerToken;

        if ($bearerToken === null) {
            $bearerToken = $this->getBearerToken();
        }

        $params = array(
            'post' => array('access_token' => $bearerToken),
            'headers' => $this->buildBearerTokenHeader(),
        );

        $response = $this->curl->send($url, $params);

        $obj = json_decode($response['body']);

        if (!$obj || !isset($obj->access_token) || $obj->access_token != $bearerToken) {
            $this->findExceptions($response);
        }

        unset($url, $bearerToken, $params, $response, $obj);

        return true;
    }


    /**
     * Generate bearer token credentials
     *
     * @return string  Bearer token credentials
     */
    protected function getBearerTokenCredentials()
    {
        $signingKey = rawurlencode($this->getConsumerKey()) . ':' . rawurlencode($this->getConsumerSecret());

        return base64_encode($signingKey);
    }

    /**
     * Getting bearer token URL
     *
     * @return string  Bearer token URL
     */
    protected function getBearerTokenUrl()
    {
        return $this->urls['domain'] . $this->urls['getToken'];
    }

    /**
     * Getting invalidate bearer token URL
     *
     * @return string  Invalidate bearer token URL
     */
    protected function getInvalidateBearerTokenUrl()
    {
        return $this->urls['domain'] . $this->urls['invToken'];
    }

    /**
     * Building request bearer token HTTP headers
     *
     * @return array  HTTP headers
     */
    protected function buildBearerTokenHeader()
    {
        return array(
            'Authorization: Basic ' . $this->getBearerTokenCredentials(),
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Expect:'
        );
    }

    /**
     * Building request HTTP headers
     *
     * @return array  HTTP headers
     */
    protected function buildRequestHeader()
    {
        $bearerToken = $this->bearerToken;

        if ($this->bearerToken === null) {
            $bearerToken = $this->getBearerToken();
        }

        return array(
            'Authorization: Bearer ' . rawurlencode($bearerToken),
            'Expect:'
        );
    }
}
