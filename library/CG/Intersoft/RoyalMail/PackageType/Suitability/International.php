<?php
namespace CG\Intersoft\RoyalMail\PackageType\Suitability;

use CG\Intersoft\RoyalMail\PackageType\SuitabilityAbstract;

class International extends SuitabilityAbstract
{
    // Keep these in size order, smallest to biggest
    // Weight in kg, dimensions in cm to match user input
    protected $limits = [
        // Letter
        'P' => [
            'weight' => 0.1, 'length' => 24, 'width' => 16.5, 'height' => 0.5,
        ],
        // Large Letter
        'G' => [
            'weight' => 0.75, 'length' => 35.3, 'width' => 25, 'height' => 2.5,
        ],
        // Parcel
        'E' => [
            'weight' => 2, 'length' => 60, 'width' => 60, 'height' => 60, 'total' => 90,
        ],
    ];

    protected function getLimits(): array
    {
        return $this->limits;
    }
}