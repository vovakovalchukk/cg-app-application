<?php
namespace Orders\Order\PickList\Image;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Message\RequestInterface as Request;
use Guzzle\Http\Exception\MultiTransferException;

class Client
{
    protected $client;

    public function __construct(GuzzleClient $client)
    {
        $this->setClient($client);
    }

    /**
     * @param Map $imageMap
     * @return Map
     */
    public function fetchImages(Map $imageMap)
    {
        $requests = [];
        foreach($imageMap as $sku => $imageUrl) {
            $requests[] = $this->createRequest($imageUrl);
        }

        try {
            $responses = $this->getClient()->send($requests);
            foreach ($responses as $response) {
                /** @var Response $response */
                $url = $response->getEffectiveUrl();
                $imageMap->setContentsForUrl($url, $response->getBody(true));
            }
            return $imageMap;
        } catch (MultiTransferException $e) {
            foreach($e->getSuccessfulRequests() as $request) {
                /** @var Request $request */
                $url = $request->getUrl();
                $imageMap->setContentsForUrl($url, $request->getResponse()->getBody(true));
            }

            foreach($e->getFailedRequests() as $request) {
                /** @var Request $request */
                $url = $request->getUrl();
                $imageMap->setContentsForUrl($url, null);
            }
            return $imageMap;
        }
    }

    protected function createRequest($imageUrl)
    {
        $request = $this->getClient()->get($imageUrl, []);
        $request->setHeader('Accept', 'image/gif, image/jpeg, image/png');
        return $request;
    }

    /**
     * @return GuzzleClient
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @param GuzzleClient $client
     * @return $this
     */
    public function setClient(GuzzleClient $client)
    {
        $this->client = $client;
        return $this;
    }
}
