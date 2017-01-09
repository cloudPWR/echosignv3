<?php
namespace Echosign\Transports;

use Echosign\Abstracts\HttpRequest;
use Echosign\Exceptions\JsonApiResponseException;
use Echosign\Interfaces\HttpTransport;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Psr7\Request;

/**
 * Class GuzzleTransport
 * @package Echosign\Transports
 */
class GuzzleTransport implements HttpTransport
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ClientException
     */
    protected $httpException;

    /**
     * @param array $config
     */
    public function __construct( array $config = [ ] )
    {
        $this->client = new Client( $config );
    }

    /**
     * @param HttpRequest $httpRequest
     * @return array|mixed
     * @throws JsonApiResponseException
     * @throws \RuntimeException
     */
    public function handleRequest( HttpRequest $httpRequest )
    {
        $options = array();
        $options['headers'] = $httpRequest->getHeaders();
        if ($httpRequest->isJsonRequest()) {
            $options['json'] = $httpRequest->getBody();
        } else {
            $options['body'] = $httpRequest->getBody();
        }
        if (isset($options['body']) && is_array($options['body'])) {
            $options['multipart'] = $options['body'];
            unset($options['body']);
        }
        

        $url = $httpRequest->getRequestUrl();

        if (empty( $url )) {
            throw new \RuntimeException( 'request url is empty.' );
        }
        
        if ($httpRequest->saveResponseToFile()) {
            $options['sink'] = $httpRequest->getFileSavePath();
        }
        
        try {
            $response = $this->client->request(
                $httpRequest->getRequestMethod(),
                $url,
                $options
            );
        } catch( ClientException $e ) {
            $this->httpException = $e;
            $response            = $e->getResponse();
        }

        return $this->handleResponse( $response );
    }

    /**
     * @param Response $response
     * @return array|mixed
     * @throws JsonApiResponseException
     */
    public function handleResponse( $response )
    {
        $contentType = $response->getHeader( 'content-type' );
        if (is_array($contentType)) {
            $contentType = array_pop($contentType);
        }
        // if its not json, then just return the response and handle it in your own object.
        if (stripos( $contentType, 'application/json' ) === false) {
            return $response;
        }

        $json = json_decode($response->getBody(), true);

        // adobe says hey this didn't work!
        if ($response->getStatusCode() >= 400) {
            // oops an error with the response, from Adobe complaining about something in your code.
            throw new JsonApiResponseException( $response->getStatusCode(), $json['message'], $json['code'] );
        }

        return $json;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return ClientException
     */
    public function getHttpException()
    {
        return $this->httpException;
    }

}