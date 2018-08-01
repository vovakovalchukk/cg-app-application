<?php
namespace Products\Listing;

use CG\Account\Shared\Entity as Account;
use CG\Ebay\CatalogApi\Client\Factory as ApiClientFactory;
use CG\Ebay\CatalogApi\Request\Search as SearchRequest;

class SearchService
{
    /** @var ApiClientFactory */
    protected $apiClientFactory;

    public function __construct(ApiClientFactory $apiClientFactory)
    {
        $this->apiClientFactory = $apiClientFactory;
    }

    public function search(Account $account, string $query)
    {
        $request = (new SearchRequest())
            ->setQuery($query)
            ->setLimit(1);

        $client = $this->apiClientFactory->createClient($account);
        $response = $client->sendRequest($request);
        var_dump($response);die;
    }
}
