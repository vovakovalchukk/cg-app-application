<?php
namespace Products\Product\Details;

use CG\Amazon\Product\ChannelDetail\External as AmazonExternalData;
use CG\ETag\Exception\NotModified;
use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\Product\ChannelDetail\Entity as ProductChannelDetail;
use CG\Product\ChannelDetail\Mapper as ProductChannelDetailMapper;
use CG\Product\ChannelDetail\Service as ProductChannelDetailService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Amazon implements ChannelInterface
{
    protected const ID = '%d-%s';
    public const CHANNEL = 'amazon';

    /** @var ProductChannelDetailService */
    protected $productChannelDetailService;
    /** @var ProductChannelDetailMapper */
    protected $productChannelDetailMapper;
    /** @var ActiveUserInterface */
    protected $activeUser;

    public function __construct(
        ProductChannelDetailService $productChannelDetailService,
        ProductChannelDetailMapper $productChannelDetailMapper,
        ActiveUserInterface $activeUser
    ) {
        $this->productChannelDetailService = $productChannelDetailService;
        $this->productChannelDetailMapper = $productChannelDetailMapper;
        $this->activeUser = $activeUser;
    }

    protected function fetchDetails(int $productId): ProductChannelDetail
    {
        return $this->productChannelDetailService->fetch(sprintf(static::ID, $productId, static::CHANNEL));
    }

    public function appendDetails(int $productId, array &$productDetails): void
    {
        try {
            $amazonDetails = $this->fetchDetails($productId)->getExternal();
            if ($amazonDetails instanceof AmazonExternalData) {
                $productDetails['fulfillmentLatency'] = $amazonDetails->getFulfillmentLatency();
            }
        } catch (NotFound $exception) {
            // No Amazon product details
        }
    }

    public function saveDetails(int $productId, array $details): void
    {
        try {
            $productChannelDetail = $this->fetchDetails($productId);
        } catch (NotFound $exception) {
            $productChannelDetail = $this->productChannelDetailMapper->fromArray([
                'productId' => $productId,
                'channel' => static::CHANNEL,
                'organisationUnitId' => $this->activeUser->getActiveUserRootOrganisationUnitId(),
            ]);
        }

        $amazonDetails = $productChannelDetail->getExternal();
        if (!($amazonDetails instanceof AmazonExternalData)) {
            $amazonDetails = new AmazonExternalData;
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
}