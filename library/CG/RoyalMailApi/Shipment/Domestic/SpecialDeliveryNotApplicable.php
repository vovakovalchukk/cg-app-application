<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;
use CG\RoyalMailApi\Shipment\Insurance\Option as InsuranceOption;
use CG\Stdlib\Exception\Runtime\NotFound;

class SpecialDeliveryNotApplicable extends NotApplicable implements
    InsuranceOptionsInterface
{
    protected static $availableInsuranceOptions = [
        '1' => 'Consequential Loss £1000',
        '2' => 'Consequential Loss £2500',
        '3' => 'Consequential Loss £5000',
        '4' => 'Consequential Loss £7500',
        '5' => 'Consequential Loss £10000',
        '11' => 'Consequential Loss £750',
    ];

    /**
     * @inheritdoc
     */
    public function isInsuranceRequired()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getInsuranceOption()
    {
        // TODO: Implement getInsuranceOption() method.
    }

    /**
     * @inheritdoc
     */
    public static function getAvailableInsuranceOptions()
    {
        $insuranceOptions = [];
        foreach (static::$availableInsuranceOptions as $reference => $displayName) {
            $insuranceOptions[] = InsuranceOption::fromArray([
               'reference' => $reference,
               'displayName' => $displayName
            ]);
        }
        return $insuranceOptions;
    }

    /**
     * @inheritdoc
     */
    public static function getInsuranceOptionByReference($reference)
    {
        if (!isset(static::$availableInsuranceOptions[$reference])) {
            throw new NotFound('No insurance option available for reference ' . $reference);
        }

        return InsuranceOption::fromArray(
            [
                'reference' => $reference,
                'displayName' => static::$availableInsuranceOptions[$reference]
            ]
        );
    }
}