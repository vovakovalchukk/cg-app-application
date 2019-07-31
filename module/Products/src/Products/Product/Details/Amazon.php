<?php
namespace Products\Product\Details;

use CG\Amazon\Product\AccountDetail\External as AmazonAccountExternalData;
use CG\Amazon\Product\ChannelDetail\External as AmazonChannelExternalData;
use CG\ETag\Exception\NotModified;
use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\Product\AccountDetail\Entity as ProductAccountDetail;
use CG\Product\AccountDetail\Mapper as ProductAccountDetailMapper;
use CG\Product\AccountDetail\Service as ProductAccountDetailService;
use CG\Product\ChannelDetail\Entity as ProductChannelDetail;
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

    protected function fetchChannelDetails(int $productId): ProductChannelDetail
    {
        return $this->productChannelDetailService->fetch(sprintf(static::CHANNEL_ID, $productId, static::CHANNEL));
    }

    protected function fetchAccountDetails(int $productId, int $accountId): ProductAccountDetail
    {
        return $this->productAccountDetailService->fetch(sprintf(static::ACCOUNT_ID, $productId, $accountId));
    }

    public function appendDetails(int $productId, array &$productDetails, array $accountIds = []): void
    {
        try {
            $amazonDetails = $this->fetchChannelDetails($productId)->getExternal();
            if ($amazonDetails instanceof AmazonChannelExternalData) {
                $productDetails['fulfillmentLatency'] = $amazonDetails->getFulfillmentLatency();
            }
        } catch (NotFound $exception) {
            // No amazon product details
        }

        foreach ($accountIds as $accountId) {
            try {
                $accountDetails = $this->fetchAccountDetails($productId, $accountId)->getExternal();
                if ($accountDetails instanceof AmazonAccountExternalData) {
                    $productDetails['fulfillmentLatency-' . $accountId] = $accountDetails->getFulfillmentLatency();
                }
            } catch (NotFound $exception) {
                // No account product details
            }
        }
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
            $productChannelDetail = $this->fetchChannelDetails($productId);
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
            $productAccountDetail = $this->fetchAccountDetails($productId, $accountId);
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