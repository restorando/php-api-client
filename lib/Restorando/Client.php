<?php

namespace Restorando;

use Buzz\Client\Curl;
use Buzz\Client\ClientInterface;

use Restorando\Api\ApiInterface;
use Restorando\Exception\InvalidArgumentException;
use Restorando\HttpClient\HttpClient;
use Restorando\HttpClient\HttpClientInterface;

class Client
{
    /**
     * Constant for authentication method. Indicates the default, but deprecated
     * login with username and token in URL.
     */
    const AUTH_URL_TOKEN = 'url_token';

    /**
     * Constant for authentication method. Not indicates the new login, but allows
     * usage of unauthenticated rate limited requests for given client_id + client_secret
     */
    const AUTH_URL_CLIENT_ID = 'url_client_id';

    /**
     * Constant for authentication method. Indicates the new login method with
     * with username and token via HTTP Authentication.
     */
    const AUTH_HTTP_TOKEN = 'http_token';

    /**
     * @var array
     */
    private $options = array(
        'base_url'    => 'https://api.restorando.com/',

        'user_agent'  => 'php-restorando-api (http://github.com/restorando/php-restorando-api)',
        'timeout'     => 10,

        'api_limit'   => 5000,
        'api_version' => null,

        'cache_dir'   => null
    );

    /**
     * The Buzz instance used to communicate with Restorando API
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Instantiate a new Restorando client
     *
     * @param null|HttpClientInterface $httpClient Restorando http client
     */
    public function __construct(HttpClientInterface $httpClient = null)
    {
        if (null !== $httpClient) {
            $this->httpClient = $httpClient;
        }
    }

    /**
     * @param string $name
     *
     * @return ApiInterface
     *
     * @throws InvalidArgumentException
     */
    public function api($name)
    {
        switch ($name) {
            case 'restaurant':
            case 'restaurants':
                $api = new Api\Restaurant($this);
                break;

            case 'reservation':
            case 'reservations':
                $api = new Api\Reservation($this);
                break;

            default:
                throw new InvalidArgumentException(sprintf('Undefined api instance called: "%s"', $name));
        }

        return $api;
    }

    /**
     * Authenticate a user for all next requests
     *
     * @param string      $tokenOrLogin  Restorando private token/username/client ID
     * @param null|string $password      Restorando password/secret (optionally can contain $authMethod)
     * @param null|string $authMethod    One of the AUTH_* class constants
     *
     * @throws InvalidArgumentException  If no authentication method was given
     */
    public function authenticate($tokenOrLogin, $password = null, $authMethod = null)
    {
        if (null === $password && null === $authMethod) {
            throw new InvalidArgumentException('You need to specify authentication method!');
        }

        if (null === $authMethod && in_array($password, array(self::AUTH_URL_TOKEN, self::AUTH_URL_CLIENT_ID, self::AUTH_HTTP_TOKEN))) {
            $authMethod = $password;
            $password   = null;
        }

        $this->getHttpClient()->authenticate($tokenOrLogin, $password, $authMethod);
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->httpClient = new HttpClient($this->options);
        }

        return $this->httpClient;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Clears used headers
     */
    public function clearHeaders()
    {
        $this->getHttpClient()->clearHeaders();
    }

    public function setRegion($country)
    {
        $this->setHeaders(array("X-REDO-REGION" => $country));
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->getHttpClient()->setHeaders($headers);
    }

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new InvalidArgumentException(sprintf('Undefined option called: "%s"', $name));
        }

        return $this->options[$name];
    }


    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new InvalidArgumentException(sprintf('Undefined option called: "%s"', $name));
        }

        if ('api_version' == $name && !in_array($value, array(null) )) {
            throw new InvalidArgumentException(sprintf('Invalid API version ("%s"), valid are: (none)', $name));
        }

        $this->options[$name] = $value;
    }
}
