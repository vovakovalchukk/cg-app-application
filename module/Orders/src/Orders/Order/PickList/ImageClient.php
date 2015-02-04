<?php
namespace Orders\Order\PickList;

use Guzzle\Http\Client as Client;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Message\RequestInterface as Request;
use Guzzle\Http\Exception\MultiTransferException;

class ImageClient
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    public function fetchImage($imageUrl)
    {
        $request = $this->getClient()->get($imageUrl, []);
        $request->setHeader('Accept', 'image/gif, image/jpeg, image/png');
        $response = $request->send();
        return $response->getBody(false);
    }

    public function fetchImages(array $imagesUrls)
    {
        $requests = [];
        foreach($imagesUrls as $imageUrl => $sku) {
            $requests[] = $this->createRequest($imageUrl);
        }

        $imagesContents = [];
        try {
            $responses = $this->getClient()->send($requests);
            foreach ($responses as $response) {
                /** @var Response $response */
                $url = $response->getEffectiveUrl();
                $imagesContents[$imagesUrls[$url]] = $response->getBody(false);
            }
            return $imagesContents;
        } catch (MultiTransferException $e) {
            foreach($e->getSuccessfulRequests() as $request) {
                /** @var Request $request */
                $url = $request->getUrl();
                $imagesContents[$imagesUrls[$url]] = $request->getResponse()->getBody(false);
            }

            foreach($e->getFailedRequests() as $request) {
                /** @var Request $request */
                $url = $request->getUrl();
                $imagesContents[$imagesUrls[$url]] = null;
            }
            return $imagesContents;
        }
    }

    protected function createRequest($imageUrl)
    {
        $request = $this->getClient()->get($imageUrl, []);
        $request->setHeader('Accept', 'image/gif, image/jpeg, image/png');
        return $request;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
