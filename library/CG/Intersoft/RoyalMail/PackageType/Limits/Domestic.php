<?php
namespace CG\Intersoft\RoyalMail\PackageType\Limits;

use CG\Intersoft\RoyalMail\PackageType\LimitsInterface;

class Domestic implements LimitsInterface
{
    // Keep these in size order, smallest to biggest
    // Weight in kg, dimensions in cm to match user input
    protected $limits = [
        // Letter
        'L' => [
            'weight' => 0.1, 'length' => 24, 'width' => 16.5, 'height' => 0.5,
        ],
        // Large Letter
        'F' => [
            'weight' => 0.75, 'length' => 35.3, 'width' => 25, 'height' => 2.5,
        ],
        // Parcel
        'P' => [
            'weight' => 2, 'length' => 45, 'width' => 35, 'height' => 16,
        ],
    ];

    public function getLimits(): array
    {
        return $this->limits;
    }
}