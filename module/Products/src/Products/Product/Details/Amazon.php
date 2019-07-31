<?php
namespace Products\Product\Details;

use CG\Amazon\Product\AccountDetail\External as AmazonAccountExternalData;
use CG\Amazon\Product\ChannelDetail\External as AmazonChannelExternalData;
use CG\ETag\Exception\NotModified;
use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\Product\AccountDetail\Entity as ProductAccountDetail;
use CG\Product\AccountDetail\Mapper as ProductAccountDetailMapper;
use CG\Product\AccountDetail\Service as ProductAccountDetailService;
use CG\Product\AccountDetail\Filter as ProductAccountDetailFilter;
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
        if (empty($productIds)) {
            return [];
        }

        $channelDetails = [];

        try {
            $productChannelDetails = $this->productChannelDetailService->fetchCollectionByFilter(
                (new ProductChannelDetailFilter('all', 1))->setId(array_map(function(int $productId) {
                    return sprintf(static::CHANNEL_ID, $productId, static::CHANNEL);
                }, $productIds))
            );

            /** @var ProductChannelDetail $productChannelDetail */
            foreach ($productChannelDetails as $productChannelDetail) {
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

        if (!empty($accountIds)) {
            try {
                $productAccountDetails = $this->productAccountDetailService->fetchCollectionByFilter(
                    (new ProductAccountDetailFilter('all', 1))->setId(array_merge(...array_map(function(int $productId) use($accountIds) {
                        $ids = [];
                        foreach ($accountIds as $accountId) {
                            $ids[] = sprintf(static::ACCOUNT_ID, $productId, $accountId);
                        }
                        return $ids;
                    }, $productIds)))
                );

                /** @var ProductAccountDetail $productAccountDetail */
                foreach ($productAccountDetails as $productAccountDetail) {
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

        return $channelDetails;
    }

    protected function fetchChannelDetail(int $productId): ProductChannelDetail
    {
        return $this->productChannelDetailService->fetch(sprintf(static::CHANNEL_ID, $productId, static::CHANNEL));
    }

    protected function fetchAccountDetail(int $productId, int $accountId): ProductAccountDetail
    {
        return $this->productAccountDetailService->fetch(sprintf(static::ACCOUNT_ID, $productId, $accountId));
    }

    public function saveDetails(int $productId, array $details, int $accountId = null): void
    {
        $accountId
            ? $this->saveAccountDetail($productId, $accountId, $details)
            : $this->saveChannelDetail($productId, $details);
    }

    protected function saveChannelDetail(int $productId, array $details): void
    {
        try {
            $productChannelDetail = $this->fetchChannelDetail($productId);
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

    protected function saveAccountDetail(int $productId, int $accountId, array $details): void
    {
        try {
            $productAccountDetail = $this->fetchAccountDetail($productId, $accountId);
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