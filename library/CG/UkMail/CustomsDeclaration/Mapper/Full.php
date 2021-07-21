<?php
namespace CG\UkMail\CustomsDeclaration\Mapper;

use CG\CourierAdapter\Provider\Implementation\Package\Content;
use CG\UkMail\CustomsDeclaration\Declaration\Article;
use CG\UkMail\CustomsDeclaration\MapperInterface;
use CG\UkMail\Shipment;
use CG\UkMail\Shipment\Package;

class Full implements MapperInterface
{
    protected const DELIVERY_AT_PLACE = 'DAP';
    protected const DELIVERY_DUTY_PAID = 'DDP';
    protected const REASON_FOR_EXPORT_COMMERCIAL_SALE = 'commercial sale';

    protected $totalValue;
    protected $currencyCode;
    protected $articles = [];

    public function toArray(Shipment $shipment): array
    {
        $this->parseDataFromContent($shipment);

        $articles = $this->getArticles();

        return [
            'invoiceType' => MapperInterface::INVOICE_TYPE_PROFORMA,
            'invoiceNumber' => '',
            'invoiceDate' => $shipment->getCollectionDate(),
            'articles' => $articles,
            'totalArticles' => count($articles),
            'shippingCharges' => 0,
            'totalValue' => $this->getTotalValue(),
            'currencyCode' => $this->getCurrencyCode(),
            'reasonForExport' => static::REASON_FOR_EXPORT_COMMERCIAL_SALE,
            'termsOfDelivery' => $this->getTermsOfDelivery($shipment),
        ];
    }

    protected function parseDataFromContent(Shipment $shipment): void
    {
        $totalValue = 0;
        $currencyCode = '';
        $articles = [];
        /** @var Package $package */
        foreach ($shipment->getPackages() as $package) {
            /** @var Content $content */
            foreach ($package->getContents() as $content) {
                $totalValue += $content->getUnitValue();
                $currencyCode = $content->getUnitCurrency();
                $articles[] = $this->getArticle($content);
            }
        }

        $this->totalValue = $totalValue;
        $this->currencyCode = $currencyCode;
        $this->articles = $articles;
    }

    protected function getTotalValue(): float
    {
        return number_format($this->totalValue, 2);
    }

    protected function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    protected function getTermsOfDelivery(Shipment $shipment): string
    {
        return $shipment->isDeliveredDutyPaid() ? static::DELIVERY_DUTY_PAID : static::DELIVERY_AT_PLACE;
    }

    protected function getArticle(Content $content): Article
    {
        return new Article(
            $content->getHsCode(),
            $content->getDescription(),
            $content->getQuantity(),
            $content->getUnitValue(),
            $content->getWeight(),
            $content->getOrigin()
        );
    }

    protected function getArticles(): array
    {
        return $this->articles;
    }
}