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
        if ($httpRequest->isJsonRequest()) {
            $request_body = json_encode($httpRequest->getBody());
        } else {
            $request_body = $httpRequest->getBody();
        }

        $url = $httpRequest->getRequestUrl();

        if (empty( $url )) {
            throw new \RuntimeException( 'request url is empty.' );
        }
        
        $options = array();
        if ($httpRequest->saveResponseToFile()) {
            $options['sink'] = $httpRequest->getFileSavePath();
        }

        $request = new Request(
            $httpRequest->getRequestMethod(),
            $url,
            $httpRequest->getHeaders(),
            $request_body
        );

        try {
            $response = $this->client->send($request, $options);
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