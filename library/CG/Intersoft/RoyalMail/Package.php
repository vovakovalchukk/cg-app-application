<?php
namespace CG\Intersoft\RoyalMail;

class Package
{
    /** @var string */
    protected $packageId;
    /** @var string */
    protected $trackingNumber;
    /** @var string */
    protected $uniqueId;
    /** @var string */
    protected $packageTrackingUrl;
    /** @var string */
    protected $formattedUniqueId;

    public function __construct(
        string $packageId,
        string $trackingNumber,
        string $uniqueId,
        string $packageTrackingUrl,
        string $formattedUniqueId
    ) {
        $this->packageId = $packageId;
        $this->trackingNumber = $trackingNumber;
        $this->uniqueId = $uniqueId;
        $this->packageTrackingUrl = $packageTrackingUrl;
        $this->formattedUniqueId = $formattedUniqueId;
    }

    public static function fromArray(array $data): Package
    {
        return new static(
            $data['packageId'],
            $data['trackingNumber'],
            $data['uniqueId'],
            $data['packageTrackingUrl'],
            $data['formattedUniqueId']
        );
    }

    public static function fromXml(\SimpleXMLElement $package): Package
    {
        return new static(
            $package->packageId,
            $package->trackingNumber,
            $package->uniqueId,
            $package->packageTrackingUrl,
            $package->formattedUniqueId
        );
    }

    public function getPackageId()
    {
        return $this->packageId;
    }

    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function getPackageTrackingUrl()
    {
        return $this->packageTrackingUrl;
    }

    public function getFormattedUniqueId()
    {
        return $this->formattedUniqueId;
    }
}