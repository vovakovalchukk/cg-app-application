<?php
namespace Products\Product\Details;

use CG\Amazon\Product\AccountDetail\External as AmazonAccountExternalData;
use CG\Amazon\Product\ChannelDetail\External as AmazonChannelExternalData;
use CG\ETag\Exception\NotModified;
use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\Product\AccountDetail\Collection as ProductAccountDetails;
use CG\Product\AccountDetail\Entity as ProductAccountDetail;
use CG\Product\AccountDetail\Filter as ProductAccountDetailFilter;
use CG\Product\AccountDetail\Mapper as ProductAccountDetailMapper;
use CG\Product\AccountDetail\Service as ProductAccountDetailService;
use CG\Product\ChannelDetail\Collection as ProductChannelDetails;
use CG\Product\ChannelDetail\Entity as ProductChannelDetail;
use CG\Product\ChannelDetail\Filter as ProductChannelDetailFilter;
use CG\Product\ChannelDetail\Mapper as ProductChannelDetailMapper;
use CG\Product\ChannelDetail\Service as ProductChannelDetailService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Amazon implements ChannelInterface
{
    public const CHANNEL = 'amazon';

    protected const CHANNEL_ID = '%d-%s';
    protected const ACCOUNT_ID = '%d-%d';
    protected const PRODUCT_ACCOUNT_DETAIL_CHUNK_LIMIT = 500;

    /** @var ProductChannelDetailService */
    protected $productChannelDetailService;
    /** @var ProductChannelDetailMapper */
    protected $productChannelDetailMapper;
    /** @var ProductAccountDetailService */
    protected $productAccountDetailService;
    /** @var ProductAccountDetailMapper */
    protected $productAccountDetailMapper;
    /** @var ActiveUserInterface */
    protected $activeUser;

    public function __construct(
        ProductChannelDetailService $productChannelDetailService,
        ProductChannelDetailMapper $productChannelDetailMapper,
        ProductAccountDetailService $productAccountDetailService,
        ProductAccountDetailMapper $productAccountDetailMapper,
        ActiveUserInterface $activeUser
    ) {
        $this->productChannelDetailService = $productChannelDetailService;
        $this->productChannelDetailMapper = $productChannelDetailMapper;
        $this->productAccountDetailService = $productAccountDetailService;
        $this->productAccountDetailMapper = $productAccountDetailMapper;
        $this->activeUser = $activeUser;
    }

    public function fetchChannelDetails(array $productIds, array $accountIds = []): array
    {
        $channelDetails = [];
        $this->appendProductChannelDetails($channelDetails, $productIds);
        $this->appendProductAccountDetails($channelDetails, $productIds, $accountIds);
        return $channelDetails;
    }

    /**
     * @return ProductChannelDetail[]
     */
    protected function fetchProductChannelDetails(array $productIds): ProductChannelDetails
    {
        if (empty($productIds)) {
            throw new NotFound();
        }

        return $this->productChannelDetailService->fetchCollectionByFilter(
            (new ProductChannelDetailFilter('all', 1))->setId(array_map(function(int $productId) {
                return sprintf(static::CHANNEL_ID, $productId, static::CHANNEL);
            }, $productIds))
        );
    }

    protected function appendProductChannelDetails(array &$channelDetails, array $productIds): void
    {
        try {
            foreach ($this->fetchProductChannelDetails($productIds) as $productChannelDetail) {
                $channelDetail = $channelDetails[$productChannelDetail->getProductId()] ?? [];

                $amazonDetails = $productChannelDetail->getExternal();
                if ($amazonDetails instanceof AmazonChannelExternalData) {
                    $channelDetail['fulfillmentLatency'] = $amazonDetails->getFulfillmentLatency();
                }

                $channelDetails[$productChannelDetail->getProductId()] = $channelDetail;
            }
        } catch (NotFound $exception) {
            // No amazon product details
        }
    }

    /**
     * @return ProductAccountDetail[]
     */
    protected function fetchProductProductAccountDetails(array $productIds, array $accountIds): ProductAccountDetails
    {
        if (empty($productIds) || empty($accountIds)) {
            throw new NotFound();
        }

        $productAccountDetailIds = array_merge(...array_map(function(int $productId) use($accountIds) {
            $ids = [];
            foreach ($accountIds as $accountId) {
                $ids[] = sprintf(static::ACCOUNT_ID, $productId, $accountId);
            }
            return $ids;
        }, $productIds));

        $productAccountDetails = new ProductAccountDetails(ProductAccountDetail::class, __METHOD__, ['id' => $productAccountDetailIds]);
        $productAccountDetailFilter = new ProductAccountDetailFilter('all', 1);
        foreach (array_chunk($productAccountDetailIds, static::PRODUCT_ACCOUNT_DETAIL_CHUNK_LIMIT) as $idBatch) {
            try {
                $productAccountDetails->attachAll($productAccountDetailFilter->setId($idBatch));
            } catch (NotFound $e) {
                //no-op
            }
        }
        return $productAccountDetails;
    }

    protected function appendProductAccountDetails(array &$channelDetails, array $productIds, array $accountIds): void
    {
        try {
            foreach ($this->fetchProductProductAccountDetails($productIds, $accountIds) as $productAccountDetail) {
                $channelDetail = $channelDetails[$productAccountDetail->getProductId()] ?? [];

                $accountDetails = $productAccountDetail->getExternal();
                if ($accountDetails instanceof AmazonAccountExternalData) {
                    $channelDetail['fulfillmentLatency-' . $productAccountDetail->getAccountId()] = $accountDetails->getFulfillmentLatency();
                }

                $channelDetails[$productAccountDetail->getProductId()] = $channelDetail;
            }
        } catch (NotFound $exception) {
            // No account product details
        }
    }

    public function saveDetails(int $productId, array $details, int $accountId = null): void
    {
        $accountId
            ? $this->saveProductAccountDetail($productId, $accountId, $details)
            : $this->saveProductChannelDetail($productId, $details);
    }

    protected function fetchProductChannelDetail(int $productId): ProductChannelDetail
    {
        return $this->productChannelDetailService->fetch(sprintf(static::CHANNEL_ID, $productId, static::CHANNEL));
    }

    protected function saveProductChannelDetail(int $productId, array $details): void
    {
        try {
            $productChannelDetail = $this->fetchProductChannelDetail($productId);
        } catch (NotFound $exception) {
            $productChannelDetail = $this->productChannelDetailMapper->fromArray([
                'productId' => $productId,
                'channel' => static::CHANNEL,
                'organisationUnitId' => $this->activeUser->getActiveUserRootOrganisationUnitId(),
            ]);
        }

        $amazonDetails = $productChannelDetail->getExternal();
        if (!($amazonDetails instanceof AmazonChannelExternalData)) {
            $amazonDetails = new AmazonChannelExternalData();
        }

        foreach ($details as $detail => $value) {
            $amazonDetails->{'set' . ucfirst($detail)}($value ?: null);
        }

        try {
            $this->productChannelDetailService->save($productChannelDetail->setExternal($amazonDetails));
        } catch (NotModified|HttpNotModified $exception) {
            // Already up to date - nothing to do
        }
    }

    protected function fetchProductAccountDetail(int $productId, int $accountId): ProductAccountDetail
    {
        return $this->productAccountDetailService->fetch(sprintf(static::ACCOUNT_ID, $productId, $accountId));
    }

    protected function saveProductAccountDetail(int $productId, int $accountId, array $details): void
    {
        try {
            $productAccountDetail = $this->fetchProductAccountDetail($productId, $accountId);
        } catch (NotFound $exception) {
            $productAccountDetail = $this->productAccountDetailMapper->fromArray([
                'productId' => $productId,
                'accountId' => $accountId,
                'organisationUnitId' => $this->activeUser->getActiveUserRootOrganisationUnitId(),
            ]);
        }

        $accountDetails = $productAccountDetail->getExternal();
        if (!($accountDetails instanceof AmazonAccountExternalData)) {
            $accountDetails = new AmazonAccountExternalData();
        }

        foreach ($details as $detail => $value) {
            $accountDetails->{'set' . ucfirst($detail)}($value ?: null);
        }

        try {
            $this->productAccountDetailService->save($productAccountDetail->setExternal($accountDetails));
        } catch (NotModified|HttpNotModified $exception) {
            // Already up to date - nothing to do
        }
    }
}