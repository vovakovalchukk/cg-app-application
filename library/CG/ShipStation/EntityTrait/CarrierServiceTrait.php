<?php
namespace CG\ShipStation\EntityTrait;

trait CarrierServiceTrait
{
    use CarrierTrait;

    /** @var  string */
    protected $serviceCode;
    /** @var  string */
    protected $name;
    /** @var  bool */
    protected $domestic;
    /** @var  bool */
    protected $international;
    /** @var  bool */
    protected $multiPackageSupported;

    public function getServiceCode(): ?string
    {
        return $this->serviceCode;
    }

    public function setServiceCode(string $serviceCode)
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function isDomestic(): ?bool
    {
        return $this->domestic;
    }

    public function setDomestic(bool $domestic)
    {
        $this->domestic = $domestic;
        return $this;
    }

    public function isInternational(): ?bool
    {
        return $this->international;
    }

    public function setInternational(bool $international)
    {
        $this->international = $international;
        return $this;
    }

    public function isMultiPackageSupported(): ?bool
    {
        return $this->multiPackageSupported;
    }

    public function setMultiPackageSupported(bool $multiPackageSupported)
    {
        $this->multiPackageSupported = $multiPackageSupported;
        return $this;
    }
}
