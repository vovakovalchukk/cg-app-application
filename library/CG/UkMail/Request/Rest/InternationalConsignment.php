<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\CustomsDeclaration\CustomsDeclarationInterface;
use CG\UkMail\InternationalConsignment\DeliveryInformation;
use CG\UkMail\InternationalConsignment\Parcel;
use CG\UkMail\InternationalConsignment\Recipient;
use CG\UkMail\DomesticConsignment\InBoxReturnDetail;
use CG\UkMail\Request\AbstractPostRequest;
use CG\UkMail\Response\Rest\InternationalConsignment as Response;

class InternationalConsignment extends AbstractPostRequest implements RequestInterface
{
    protected const URI = 'v3/internationalconsignment/internationalconsignment';

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
    /** @var string|null */
    protected $customerReference;
    /** @var string|null */
    protected $alternativeReference;
    /** @var Parcel[] */
    protected $parcels;
    /** @var bool */
    protected $extendedCoverRequired;
    /** @var string|null */
    protected $iossNumber;
    /** @var CustomsDeclarationInterface */
    protected $customsDeclaration;
    /** @var Recipient */
    protected $recipient;
    /** @var bool|null */
    protected $inBoxReturn;
    /** @var InBoxReturnDetail|null */
    protected $inBoxReturnDetail;
    /** @var bool */
    protected $invoiceRequired;
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
        ?string $customerReference,
        ?string $alternativeReference,
        array $parcels,
        bool $extendedCoverRequired,
        ?string $iossNumber,
        CustomsDeclarationInterface $customsDeclaration,
        Recipient $recipient,
        ?bool $inBoxReturn,
        ?InBoxReturnDetail $inBoxReturnDetail,
        bool $invoiceRequired,
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
        $this->customerReference = $customerReference;
        $this->alternativeReference = $alternativeReference;
        $this->parcels = $parcels;
        $this->extendedCoverRequired = $extendedCoverRequired;
        $this->iossNumber = $iossNumber;
        $this->customsDeclaration = $customsDeclaration;
        $this->recipient = $recipient;
        $this->inBoxReturn = $inBoxReturn;
        $this->inBoxReturnDetail = $inBoxReturnDetail;
        $this->invoiceRequired = $invoiceRequired;
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
            'customerReference' => $this->getCustomerReference(),
            'alternativeReference' => $this->getAlternativeReference(),
            'extendedCoverRequired' => $this->isExtendedCoverRequired(),
            'IOSSNumber' => $this->getIossNumber(),
            'customsDeclaration' => $this->getCustomsDeclaration()->toArray(),
            'recipient' => $this->getRecipient()->toArray(),
            'inBoxReturn' => $this->isInBoxReturn(),
            'inboxReturnDetail' => $this->getInBoxReturnDetail() != null ? $this->getInBoxReturnDetail()->toArray() : null,
            'invoiceRequired' => $this->isInvoiceRequired(),
            'labelFormat' => $this->getLabelFormat(),
        ];

        /** @var Parcel $parcel */
        foreach ($this->getParcels() as $parcel) {
            $body['parcels'][] = $parcel->toArray();
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

    public function setApiKey(string $apiKey): InternationalConsignment
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): InternationalConsignment
    {
        $this->username = $username;
        return $this;
    }

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(string $authenticationToken): InternationalConsignment
    {
        $this->authenticationToken = $authenticationToken;
        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): InternationalConsignment
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getCollectionJobNumber(): string
    {
        return $this->collectionJobNumber;
    }

    public function setCollectionJobNumber(string $collectionJobNumber): InternationalConsignment
    {
        $this->collectionJobNumber = $collectionJobNumber;
        return $this;
    }

    public function getCollectionDate(): \DateTime
    {
        return $this->collectionDate;
    }

    public function setCollectionDate(\DateTime $collectionDate): InternationalConsignment
    {
        $this->collectionDate = $collectionDate;
        return $this;
    }

    public function getDeliveryDetails(): DeliveryInformation
    {
        return $this->deliveryDetails;
    }

    public function setDeliveryDetails(DeliveryInformation $deliveryDetails): InternationalConsignment
    {
        $this->deliveryDetails = $deliveryDetails;
        return $this;
    }

    public function getServiceKey(): string
    {
        return $this->serviceKey;
    }

    public function setServiceKey(string $serviceKey): InternationalConsignment
    {
        $this->serviceKey = $serviceKey;
        return $this;
    }

    public function getItems(): int
    {
        return $this->items;
    }

    public function setItems(int $items): InternationalConsignment
    {
        $this->items = $items;
        return $this;
    }

    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    public function setCustomerReference(?string $customerReference): InternationalConsignment
    {
        $this->customerReference = $customerReference;
        return $this;
    }

    public function getAlternativeReference(): ?string
    {
        return $this->alternativeReference;
    }

    public function setAlternativeReference(?string $alternativeReference): InternationalConsignment
    {
        $this->alternativeReference = $alternativeReference;
        return $this;
    }

    /**
     * @return Parcel[]
     */
    public function getParcels(): array
    {
        return $this->parcels;
    }

    /**
     * @param Parcel[] $parcels
     * @return DomesticConsignment
     */
    public function setParcels(array $parcels): InternationalConsignment
    {
        $this->parcels = $parcels;
        return $this;
    }

    public function isExtendedCoverRequired(): bool
    {
        return $this->extendedCoverRequired;
    }

    public function setExtendedCoverRequired(bool $extendedCoverRequired): InternationalConsignment
    {
        $this->extendedCoverRequired = $extendedCoverRequired;
        return $this;
    }

    public function getIossNumber(): ?string
    {
        return $this->iossNumber;
    }

    public function setIossNumber(?string $iossNumber): InternationalConsignment
    {
        $this->iossNumber = $iossNumber;
        return $this;
    }

    public function getCustomsDeclaration(): CustomsDeclarationInterface
    {
        return $this->customsDeclaration;
    }

    public function setCustomsDeclaration(CustomsDeclarationInterface $customsDeclaration): InternationalConsignment
    {
        $this->customsDeclaration = $customsDeclaration;
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

    public function isInvoiceRequired(): bool
    {
        return $this->invoiceRequired;
    }

    public function setInvoiceRequired(bool $invoiceRequired): InternationalConsignment
    {
        $this->invoiceRequired = $invoiceRequired;
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