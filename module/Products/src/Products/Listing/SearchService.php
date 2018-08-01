<?php
namespace Products\Listing;

use CG\Account\Shared\Entity as Account;
use CG\Ebay\CatalogApi\Client\Factory as ApiClientFactory;
use CG\Ebay\CatalogApi\Request\Search as SearchRequest;
use CG\Ebay\CatalogApi\Response\Product\Aspect;
use CG\Ebay\CatalogApi\Response\Search as SearchResponse;

class SearchService
{
    const DEFAULT_LIMIT = 10;

    /** @var ApiClientFactory */
    protected $apiClientFactory;

    public function __construct(ApiClientFactory $apiClientFactory)
    {
        $this->apiClientFactory = $apiClientFactory;
    }

    public function search(Account $account, string $query): array
    {
        $client = $this->apiClientFactory->createClient($account);

        try {
            $request = $this->buildSearchRequest($query);
            $response = $client->sendRequest($request);
        } catch (SearchException $exception) {
            /** @var SearchResponse $response */
            $response = $client->sendRequest($this->buildMpnRequest($query, self::DEFAULT_LIMIT/2));
            /** @var SearchResponse $responseForQuery */
            $responseForQuery = $client->sendRequest($this->buildQueryRequest($query, self::DEFAULT_LIMIT/2));
            foreach ($responseForQuery->getProductSummaries() as $summary) {
                $response->addProductSummary($summary);
            }
        }

        return $this->formatResponseAsArray($response);
    }

    protected function buildSearchRequest(string $query)
    {
        if ($this->isQueryGtin($query)) {
            return $this->buildGtinRequest($query);
        }

        if ($this->isSingleWord($query)) {
            throw new SearchException('The provided query string can be either an MPN or a query param, two requests are needed');
        }

        return $this->buildQueryRequest($query);
    }

    protected function isQueryGtin(string $query): bool
    {
        $length = strlen($query);
        return $length < 15 && $length > 7 && is_numeric($query);
    }

    protected function isSingleWord(string $query): bool
    {
        return strpos($query, ' ') === false;
    }

    protected function buildGtinRequest(string $query, int $limit = self::DEFAULT_LIMIT): SearchRequest
    {
        return $this->buildSearchRequestWithLimit($limit)->setGtin($query);
    }

    protected function buildMpnRequest(string $query, int $limit = self::DEFAULT_LIMIT): SearchRequest
    {
        return $this->buildSearchRequestWithLimit($limit)->setMpn($query);
    }

    protected function buildQueryRequest(string $query, int $limit = self::DEFAULT_LIMIT): SearchRequest
    {
        return $this->buildSearchRequestWithLimit($limit)->setQuery($query);
    }

    protected function buildSearchRequestWithLimit(int $limit = self::DEFAULT_LIMIT): SearchRequest
    {
        return (new SearchRequest())
            ->setLimit($limit);
    }

    protected function formatResponseAsArray(SearchResponse $response): array
    {
        $array = [];
        foreach ($response->getProductSummaries() as $summary) {
            $array[] = [
                'epid' => $summary->getEpid(),
                'ean' => $summary->getEan()[0] ?? null,
                'upc' => $summary->getUpc()[0] ?? null,
                'isbn' => $summary->getIsbn()[0] ?? null,
                'brand' => $summary->getBrand(),
                'mpn' => $summary->getMpn()[0] ?? null,
                'title' => $summary->getTitle(),
                'imageUrl' => $summary->getImage(),
                'itemSpecifics' => $this->formatItemSpecificsArray($summary->getAspects())
            ];
        }
        return $array;
    }

    /**
     * @param Aspect[] $aspects
     * @return array
     */
    protected function formatItemSpecificsArray(array $aspects): array
    {
        $array = [];
        foreach ($aspects as $aspect) {
            $array[$aspect->getName()] = count($aspect->getValues()) === 1 ? $aspect->getValues()[0] : $aspect->getValues();
        }
        return $array;
    }
}
