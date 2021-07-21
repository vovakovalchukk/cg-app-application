<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\DomesticConsignment\DeliveryInformation;
use CG\UkMail\DomesticConsignment\Parcel;
use CG\UkMail\DomesticConsignment\Recipient;
use CG\UkMail\DomesticConsignment\InBoxReturnDetail;
use CG\UkMail\Request\AbstractPostRequest;
use CG\UkMail\Response\Rest\DomesticConsignment as Response;

class DomesticConsignment extends AbstractPostRequest implements RequestInterface
{
    protected const URI = 'gateway/DomesticConsignment/2.0/DomesticConsignment';

    /** @var string */
    protected $apiKey;
    /** @var string */
    protected $username;
    /** @var string */
    protected $authenticationToken;
    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $collectionJobNumber;
    /** @var \DateTime */
    protected $collectionDate;
    /** @var DeliveryInformation */
    protected $deliveryDetails;
    /** @var string */
    protected $serviceKey;
    /** @var int */
    protected $items;
    /** @var int */
    protected $totalWeight;
    /** @var string|null */
    protected $customerReference;
    /** @var string|null */
    protected $alternativeReference;
    /** @var Parcel[] */
    protected $parcels;
    /** @var int|null */
    protected $extendedCoverUnits;
    /** @var Recipient */
    protected $recipient;
    /** @var bool|null */
    protected $exchangeOnDelivery;
    /** @var bool|null */
    protected $bookin;
    /** @var bool|null */
    protected $inBoxReturn;
    /** @var InBoxReturnDetail|null */
    protected $inBoxReturnDetail;
    /** @var string */
    protected $labelFormat;

    public function __construct(
        string $apiKey,
        string $username,
        string $authenticationToken,
        string $accountNumber,
        string $collectionJobNumber,
        \DateTime $collectionDate,
        DeliveryInformation $deliveryDetails,
        string $serviceKey,
        int $items,
        int $totalWeight,
        ?string $customerReference,
        ?string $alternativeReference,
        ?array $parcels,
        ?int $extendedCoverUnits,
        Recipient $recipient,
        ?bool $exchangeOnDelivery,
        ?bool $bookin,
        ?bool $inBoxReturn,
        ?InBoxReturnDetail $inBoxReturnDetail,
        string $labelFormat
    ) {
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->authenticationToken = $authenticationToken;
        $this->accountNumber = $accountNumber;
        $this->collectionJobNumber = $collectionJobNumber;
        $this->collectionDate = $collectionDate;
        $this->deliveryDetails = $deliveryDetails;
        $this->serviceKey = $serviceKey;
        $this->items = $items;
        $this->totalWeight = $totalWeight;
        $this->customerReference = $customerReference;
        $this->alternativeReference = $alternativeReference;
        $this->parcels = $parcels;
        $this->extendedCoverUnits = $extendedCoverUnits;
        $this->recipient = $recipient;
        $this->exchangeOnDelivery = $exchangeOnDelivery;
        $this->bookin = $bookin;
        $this->inBoxReturn = $inBoxReturn;
        $this->inBoxReturnDetail = $inBoxReturnDetail;
        $this->labelFormat = $labelFormat;
    }

    protected function getBody(): array
    {
        $body = [
            'userName' => $this->getUsername(),
            'authenticationToken' => $this->getAuthenticationToken(),
            'accountNumber' => $this->getAccountNumber(),
            'collectionInfo' => [
                'collectionJobNumber' => $this->getCollectionJobNumber(),
                'collectionDate' => $this->getCollectionDate()->format('Y-m-d'),
            ],
            'delivery' => $this->getDeliveryDetails()->toArray(),
            'serviceKey' => $this->getServiceKey(),
            'items' => $this->getItems(),
            'totalWeight' => $this->getTotalWeight(), //weight in whole Kg
            'customerReference' => $this->getCustomerReference(),
            'alternativeReference' => $this->getAlternativeReference(),
            'extendedCoverUnits' => $this->getExtendedCoverUnits(),
            'recipient' => $this->getRecipient()->toArray(),
            'exchangeOnDelivery' => $this->isExchangeOnDelivery(),
            'bookin' => $this->isBookin(),
            'inBoxReturn' => $this->isInBoxReturn(),
            'inboxReturnDetail' => $this->getInBoxReturnDetail() != null ? $this->getInBoxReturnDetail()->toArray() : null,
            'labelFormat' => $this->getLabelFormat(),
        ];

        $parcels = $this->getParcels();
        if (isset($parcels) && is_array($parcels)) {
            $body['parcels'] = null;
            /** @var Parcel $parcel */
            foreach ($parcels as $parcel) {
                $body['parcels'][] = $parcel->toArray();
            }
        }

        return $body;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ];
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);
        return [
            'headers' => array_merge($options['headers'] ?? [], $this->getHeaders()),
            'json' => $this->getBody()
        ];
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): DomesticConsignment
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): DomesticConsignment
    {
        $this->username = $username;
        return $this;
    }

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(string $authenticationToken): DomesticConsignment
    {
        $this->authenticationToken = $authenticationToken;
        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): DomesticConsignment
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getCollectionJobNumber(): string
    {
        return $this->collectionJobNumber;
    }

    public function setCollectionJobNumber(string $collectionJobNumber): DomesticConsignment
    {
        $this->collectionJobNumber = $collectionJobNumber;
        return $this;
    }

    public function getCollectionDate(): \DateTime
    {
        return $this->collectionDate;
    }

    public function setCollectionDate(\DateTime $collectionDate): DomesticConsignment
    {
        $this->collectionDate = $collectionDate;
        return $this;
    }

    public function getDeliveryDetails(): DeliveryInformation
    {
        return $this->deliveryDetails;
    }

    public function setDeliveryDetails(DeliveryInformation $deliveryDetails): DomesticConsignment
    {
        $this->deliveryDetails = $deliveryDetails;
        return $this;
    }

    public function getServiceKey(): string
    {
        return $this->serviceKey;
    }

    public function setServiceKey(string $serviceKey): DomesticConsignment
    {
        $this->serviceKey = $serviceKey;
        return $this;
    }

    public function getItems(): int
    {
        return $this->items;
    }

    public function setItems(int $items): DomesticConsignment
    {
        $this->items = $items;
        return $this;
    }

    public function getTotalWeight(): int
    {
        return $this->totalWeight;
    }

    public function setTotalWeight(int $totalWeight): DomesticConsignment
    {
        $this->totalWeight = $totalWeight;
        return $this;
    }

    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    public function setCustomerReference(?string $customerReference): DomesticConsignment
    {
        $this->customerReference = $customerReference;
        return $this;
    }

    public function getAlternativeReference(): ?string
    {
        return $this->alternativeReference;
    }

    public function setAlternativeReference(?string $alternativeReference): DomesticConsignment
    {
        $this->alternativeReference = $alternativeReference;
        return $this;
    }

    /**
     * @return Parcel[]
     */
    public function getParcels(): ?array
    {
        return $this->parcels;
    }

    /**
     * @param Parcel[] $parcels
     * @return DomesticConsignment
     */
    public function setParcels(?array $parcels): DomesticConsignment
    {
        $this->parcels = $parcels;
        return $this;
    }

    public function getExtendedCoverUnits(): ?int
    {
        return $this->extendedCoverUnits;
    }

    public function setExtendedCoverUnits(?int $extendedCoverUnits): DomesticConsignment
    {
        $this->extendedCoverUnits = $extendedCoverUnits;
        return $this;
    }

    public function getRecipient(): Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(Recipient $recipient): DomesticConsignment
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function isExchangeOnDelivery(): ?bool
    {
        return $this->exchangeOnDelivery;
    }

    public function setExchangeOnDelivery(?bool $exchangeOnDelivery): DomesticConsignment
    {
        $this->exchangeOnDelivery = $exchangeOnDelivery;
        return $this;
    }

    public function isBookin(): ?bool
    {
        return $this->bookin;
    }

    public function setBookin(?bool $bookin): DomesticConsignment
    {
        $this->bookin = $bookin;
        return $this;
    }

    public function isInBoxReturn(): ?bool
    {
        return $this->inBoxReturn;
    }

    public function setInBoxReturn(?bool $inBoxReturn): DomesticConsignment
    {
        $this->inBoxReturn = $inBoxReturn;
        return $this;
    }

    public function getInBoxReturnDetail(): ?InBoxReturnDetail
    {
        return $this->inBoxReturnDetail;
    }

    public function setInBoxReturnDetail(?InBoxReturnDetail $inBoxReturnDetail): DomesticConsignment
    {
        $this->inBoxReturnDetail = $inBoxReturnDetail;
        return $this;
    }

    public function getLabelFormat(): string
    {
        return $this->labelFormat;
    }

    public function setLabelFormat(string $labelFormat): DomesticConsignment
    {
        $this->labelFormat = $labelFormat;
        return $this;
    }
}