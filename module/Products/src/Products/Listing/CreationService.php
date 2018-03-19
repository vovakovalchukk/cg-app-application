<?php
namespace Products\Listing;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Product\Listing\CreatorInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Products\Listing\CreationService\Status as CreationStatus;
use Products\Listing\CreationService\UnsupportedChannelException;
use Zend\Di\Di;
use Zend\Di\Exception\ExceptionInterface as DiException;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

class CreationService implements LoggerAwareInterface
{
    use LogTrait;

    /** @var AccountService */
    protected $accountService;
    /** @var ProductService */
    protected $productService;
    /** @var Di */
    protected $di;
    /** @var CategoryService  */
    protected $categoryService;

    public function __construct(
        AccountService $accountService,
        ProductService $productService,
        Di $di,
        CategoryService $categoryService
    ) {
        $this->accountService = $accountService;
        $this->productService = $productService;
        $this->di = $di;
        $this->categoryService = $categoryService;
    }

    public function createListing(CreationStatus $status, int $accountId, int $productId, array $listing)
    {
        $this->removeGlobalLogEventParams(['account' => $accountId, 'product' => $productId]);
        try {
            $listing = $this->sanitiseListingData($listing);
            try {
                $account = $this->fetchAccount($accountId);
            } catch (NotFound $exception) {
                $status->error('Please select a valid account');
                return;
            }

            try {
                $product = $this->fetchProduct($productId);
            } catch (NotFound $exception) {
                $status->error('Please select a valid product');
                return;
            }

            try {
                $variations = $this->fetchVariationsForListingData($listing);
            } catch (NotFound $exception) {
                $status->error('Please select valid variation products');
                return;
            }

            try {
                $creator = $this->getCreatorForChannel($account->getChannel());
            } catch (UnsupportedChannelException $exception) {
                $status->error('Channel is not supported');
                return;
            }

            $listing = $this->formatListingData($listing);
            if (!$variations) {
                $result = $creator->createListing($account, $product, $listing);
            } else {
                $result = $creator->createVariationListing($account, $product, $variations, $listing);
            }

            foreach ($result->getWarnings() as $warning) {
                $status->warning($warning);
            }
            foreach ($result->getErrors() as $error) {
                $status->error($error);
            }
            if ($result->getExternalId()) {
                $status->success();
            }
        } finally {
            $this->removeGlobalLogEventParams(['account', 'product']);
        }
    }

    protected function sanitiseListingData(array $listing): array
    {
        // The frontend converts null to empty string, ensure we've actually got an array
        if (isset($listing['variations']) && !is_array($listing['variations'])) {
            unset($listing['variations']);
        }
        return $listing;
    }

    protected function fetchAccount(int $accountId): Account
    {
        return $this->accountService->fetch($accountId);
    }

    protected function fetchProduct(int $productId): Product
    {
        return $this->productService->fetch($productId);
    }

    protected function fetchVariationsForListingData(array $listing): ?ProductCollection
    {
        if (!isset($listing['variations'])) {
            return null;
        }

        $filter = $this->buildFilterForVariationIds(array_keys($listing['variations']));
        return $this->productService->fetchCollectionByFilter($filter);
    }

    protected function buildFilterForVariationIds(array $variationIds): ProductFilter
    {
        return (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($variationIds);
    }

    protected function getCreatorForChannel(string $channel): CreatorInterface
    {
        $channelNamespace = hyphenToFullyQualifiedClassname($channel, 'CG');
        try {
            $creator = $this->di->newInstance($channelNamespace . '\\Listing\\Creator');
            if (!($creator instanceof CreatorInterface)) {
                throw new UnsupportedChannelException(
                    sprintf(
                        'Listing Creator (%s) for channel %s is not an instance of %s',
                        get_class($creator),
                        $channel,
                        CreatorInterface::class
                    )
                );
            }
            return $creator;
        } catch (DiException $exception) {
            throw new UnsupportedChannelException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    protected function formatListingData(array $listing): array
    {
        if (!isset($listing['category'])) {
            return $listing;
        }
        $categoryId = (int) $listing['category'];
        try {
            /** @var Category $category */
            $category = $this->categoryService->fetch($categoryId);
            $listing['category'] = $category->getExternalId();
        } catch (NotFound $e) {
            unset($listing['category']);
        }

        return $listing;
    }
}
