<?php
namespace Products\Controller\CreateListings;

use Application\Controller\AbstractJsonController;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Channel\Amazon\Service as AmazonListingChannelService;
use Products\Listing\Exception as ListingException;

class AmazonJsonController extends AbstractJsonController
{
    public const ROUTE_AMAZON_CATEGORY_DEPENDENT_FIELD_VALUES = 'AmazonCategoryDependentFieldValues';

    /** @var AmazonListingChannelService */
    protected $amazonListingChannelService;

    public function __construct(JsonModelFactory $jsonModelFactory, AmazonListingChannelService $amazonListingChannelService)
    {
        parent::__construct($jsonModelFactory);
        $this->amazonListingChannelService = $amazonListingChannelService;
    }

    public function amazonCategoryDependentFieldValuesAction()
    {
        try {
            $amazonCategoryId = $this->getAmazonCategoryIdFromRoute();
            return $this->buildResponse(
                $this->amazonListingChannelService->getAmazonCategoryDependentValues($amazonCategoryId)
            );
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse($e);
        }
    }

    protected function getAmazonCategoryIdFromRoute(): int
    {
        return (int)$this->params()->fromRoute('amazonCategoryId', -1);
    }

    protected function buildGenericErrorResponse(\Throwable $e)
    {
        $this->logErrorException($e);
        return $this->buildErrorResponse('An error has occurred. Please try again');
    }
}