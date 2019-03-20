<?php

use CG\RoyalMailApi\DeliveryService\Service as DeliveryServiceService;
use CG\RoyalMailApi\Shipment as Shipment;
use CG\RoyalMailApi\Shipment\International\LetterLargeLetterParcel as InternationalLetterLargeLetterParcelShipment;
use CG\RoyalMailApi\Shipment\Domestic\LetterLargeLetterParcel as DomesticLetterLargeLetterParcelShipment;
use CG\RoyalMailApi\Shipment\International\LetterLargeLetter as InternationalLetterLargeLetterShipment;
use CG\RoyalMailApi\Shipment\Domestic\LetterLargeLetter as DomesticLetterLargeLetterShipment;
use CG\RoyalMailApi\Shipment\International\NotApplicable as InternationalNotApplicableShipment;
use CG\RoyalMailApi\Shipment\Domestic\NotApplicable as DomesticNotApplicableShipment;
use CG\RoyalMailApi\Shipment\Domestic\LargeLetterParcel as DomesticLargeLetterParcelShipment;
use CG\RoyalMailApi\Shipment\International\LargeLetterParcel as InternationalLargeLetterParcelShipment;
use CG\RoyalMailApi\Shipment\International\Parcel as InternationalParcelShipment;
use CG\RoyalMailApi\Shipment\International\LargeLetter as InternationalLargeLetterShipment;
use CG\RoyalMailApi\Shipment\Domestic\LargeLetter as DomesticLargeLetterShipment;
use CG\RoyalMailApi\Shipment\International\NotApplicableParcel as InternationalNotApplicableParcelShipment;
use CG\RoyalMailApi\Shipment\Domestic\SpecialDeliveryNotApplicable as DomesticSpecialDeliveryNotApplicable;

return [
    'di' => [
        'instance' => [
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        'serviceOfferings' => [
                            'BF1' => [
                                'displayName' => 'HM Forces Mail'
                            ],
                            'BF2' => [
                                'displayName' => 'HM Forces Signed For'
                            ],
                            'BF7' => [
                                'displayName' => 'HM Forces Special Delivery (£500)'
                            ],
                            'BF8' => [
                                'displayName' => 'HM Forces Special Delivery (£1000)'
                            ],
                            'BF9' => [
                                'displayName' => 'HM Forces Special Delivery (£2500)'
                            ],
                            'BPL' => [
                                'displayName' => 'Royal Mail 1st/2nd Class'
                            ],
                            'BPR' => [
                                'displayName' => 'Royal Mail 1st/2nd Class Signed For'
                            ],
                            'CRL' => [
                                'displayName' => 'Royal Mail 24/ Royal Mail 48 Standard/Signed For (Parcel - Daily Rate Service)'
                            ],
                            'DE1' => [
                                'displayName' => 'International Business Parcels Zero Sort High Volume Priority'
                            ],
                            'DE3' => [
                                'displayName' => 'International Business Parcels Zero Sort High Vol Economy'
                            ],
                            'DE4' => [
                                'displayName' => 'International Business Parcels Zero Srt Low Volume Priority'
                            ],
                            'DE6' => [
                                'displayName' => 'International Business Parcels Zero Sort Low Vol Economy'
                            ],
                            'DG1' => [
                                'displayName' => 'International Business Mail Large Letter Country Sort High Volume Priority'
                            ],
                            'DG3' => [
                                'displayName' => 'International Business Mail Large Letter Ctry Sort High Vol Economy'
                            ],
                            'DG4' => [
                                'displayName' => 'International Business Mail Large Letter Country Sort Low Volume Priority'
                            ],
                            'DG6' => [
                                'displayName' => 'International Business Mail Large Letter Ctry Sort Low Vol Economy'
                            ],
                            'FS1' => [
                                'displayName' => 'Royal Mail 24 Standard/Signed For Large Letter (Flat Rate Service)'
                            ],
                            'FS2' => [
                                'displayName' => 'Royal Mail 48 Standard/Signed For Large Letter (Flat Rate Service)'
                            ],
                            'FS7' => [
                                'displayName' => 'Royal Mail 24 (Presorted) (Large Letter)'
                            ],
                            'FS8' => [
                                'displayName' => 'Royal Mail 48 (Presorted) (Large Letter)'
                            ],
                            'IE1' => [
                                'displayName' => 'International Business Parcels Zone Sort Priority Service'
                            ],
                            'IE3' => [
                                'displayName' => 'International Business Parcels Zone Sort Economy Service'
                            ],
                            'IG1' => [
                                'displayName' => 'International Business Mail Large Letter Zone Sort Priority'
                            ],
                            'IG3' => [
                                'displayName' => 'International Business Mail Large Letter Zone Sort Economy'
                            ],
                            'IG4' => [
                                'displayName' => 'International Business Mail Large Letter Zone Sort Priority Machine'
                            ],
                            'IG6' => [
                                'displayName' => 'International Business Mail Large Letter Zone Srt Economy Machine'
                            ],
                            'LA1' => [
                                'displayName' => 'Special Delivery Guaranteed By 1PM LA (£500)'
                            ],
                            'LA2' => [
                                'displayName' => 'Special Delivery Guaranteed By 1PM LA (£1000)'
                            ],
                            'LA3' => [
                                'displayName' => 'Special Delivery Guaranteed By 1PM LA (£2500)'
                            ],
                            'LA4' => [
                                'displayName' => 'Special Delivery Guaranteed By 9AM LA (£50)'
                            ],
                            'LA5' => [
                                'displayName' => 'Special Delivery Guaranteed By 9AM LA (£1000)'
                            ],
                            'LA6' => [
                                'displayName' => 'Special Delivery Guaranteed By 9AM LA (£2500)'
                            ],
                            'MB1' => [
                                'displayName' => 'INTL BUS PARCELS PRINT DIRECT PRIORITY'
                            ],
                            'MB2' => [
                                'displayName' => 'INTL BUS PARCELS PRINT DIRECT STANDARD'
                            ],
                            'MB3' => [
                                'displayName' => 'INTL BUS PARCELS PRINT DIRECT ECONOMY'
                            ],
                            'MP0' => [
                                'displayName' => 'International Business Parcels Signed Extra Compensation (Country Pricing)'
                            ],
                            'MP1' => [
                                'displayName' => 'International Business Parcels Tracked (Zonal Pricing)'
                            ],
                            'MP4' => [
                                'displayName' => 'International Business Parcels Tracked Extra Comp (Zonal Pricing)'
                            ],
                            'MP5' => [
                                'displayName' => 'International Business Parcels Signed (Zonal Pricing)'
                            ],
                            'MP6' => [
                                'displayName' => 'International Business Parcels Signed Extra Compensation (Zonal Pricing)'
                            ],
                            'MP7' => [
                                'displayName' => 'International Business Parcels Tracked (Country Pricing)'
                            ],
                            'MP8' => [
                                'displayName' => 'International Business Parcels Tracked Extra Comp (Country Pricing)'
                            ],
                            'MP9' => [
                                'displayName' => 'International Business Parcels Signed (Country Pricing)'
                            ],
                            'MPB' => [
                                'displayName' => 'International Business Parcel Tracked Boxable Extra Comp (Country Pricing)'
                            ],
                            'MPF' => [
                                'displayName' => 'International Business Parcel Tracked High Vol. (Country Pricing)'
                            ],
                            'MPG' => [
                                'displayName' => 'International Business Parcels Tracked & Signed High Vol. (Country Pricing)'
                            ],
                            'MPH' => [
                                'displayName' => 'International Business Parcel Signed High Vol. (Country Pricing)'
                            ],
                            'MPI' => [
                                'displayName' => 'International Business Parcel Tracked High Vol. Extra Comp (Country Pricing)'
                            ],
                            'MPJ' => [
                                'displayName' => 'International Business Parcels Tracked & Signed High Vol. Extra Comp (Country Pricing)'
                            ],
                            'MPK' => [
                                'displayName' => 'International Business Parcel Signed High Vol. Extra Comp (Country Pricing)'
                            ],
                            'MPL' => [
                                'displayName' => 'International Business Mail Tracked High Vol. (Country Pricing)'
                            ],
                            'MPM' => [
                                'displayName' => 'International Business Mail Tracked & Signed High Vol. (Country Pricing)'
                            ],
                            'MPN' => [
                                'displayName' => 'International Business Mail Signed High Vol. (Country Pricing)'
                            ],
                            'MPO' => [
                                'displayName' => 'International Business Mail Tracked High Vol. Extra Comp (Country Pricing)'
                            ],
                            'MPP' => [
                                'displayName' => 'International Business Mail Tracked & Signed High Vol. Extra Comp (Country Pricing)'
                            ],
                            'MPQ' => [
                                'displayName' => 'International Business Mail Signed High Vol. Extra Comp (Country Pricing)'
                            ],
                            'MPR' => [
                                'displayName' => 'International Business Parcel Tracked Boxable (Country Pricing)'
                            ],
                            'MPT' => [
                                'displayName' => 'International Business Parcel Tracked Boxable High Vol. (Country Pricing)'
                            ],
                            'MPU' => [
                                'displayName' => 'International Business Parcel Tracked Boxable Extra Comp (Country Pricing)'
                            ],
                            'MPV' => [
                                'displayName' => 'International Business Parcel Zero Sort Boxable Low Vol. Priority'
                            ],
                            'MPW' => [
                                'displayName' => 'International Business Parcel Zero Sort Boxable Low Vol. Economy'
                            ],
                            'MPX' => [
                                'displayName' => 'International Business Parcel Zero Sort Boxable High Vol. Priority'
                            ],
                            'MPY' => [
                                'displayName' => 'International Business Parcel Zero Sort Boxable High Vol. Economy'
                            ],
                            'MTA' => [
                                'displayName' => 'International Business Parcels Tracked & Signed (Zonal Pricing)'
                            ],
                            'MTB' => [
                                'displayName' => 'International Business Parcels Tracked & Signed Extra Compensation (Zonal Pricing)'
                            ],
                            'MTC' => [
                                'displayName' => 'International Business Mail Tracked & Signed (Zonal Pricing)'
                            ],
                            'MTD' => [
                                'displayName' => 'International Business Mail Tracked & Signed Extra Compensation (Zonal Pricing)'
                            ],
                            'MTE' => [
                                'displayName' => 'International Business Parcels Tracked & Signed (Country Pricing)'
                            ],
                            'MTF' => [
                                'displayName' => 'International Business Parcels Tracked & Signed Extra Compensation (Country Pricing)'
                            ],
                            'MTG' => [
                                'displayName' => 'International Business Mail Tracked & Signed (Country Pricing)'
                            ],
                            'MTH' => [
                                'displayName' => 'International Business Mail Tracked & Signed Extra Compensation (Country Pricing)'
                            ],
                            'MTI' => [
                                'displayName' => 'International Business Mail Tracked (Zonal Pricing)'
                            ],
                            'MTJ' => [
                                'displayName' => 'International Business Mail Tracked Extra Comp (Zonal Pricing)'
                            ],
                            'MTK' => [
                                'displayName' => 'International Business Mail Tracked (Country Pricing)'
                            ],
                            'MTL' => [
                                'displayName' => 'International Business Mail Tracked Extra Comp (Country Pricing)'
                            ],
                            'MTM' => [
                                'displayName' => 'International Business Mail Signed (Zonal Pricing)'
                            ],
                            'MTN' => [
                                'displayName' => 'International Business Mail Signed Extra Compensation (Zonal Pricing)'
                            ],
                            'MTO' => [
                                'displayName' => 'International Business Mail Signed (Country Pricing)'
                            ],
                            'MTP' => [
                                'displayName' => 'International Business Mail Signed Extra Compensation (Country Pricing)'
                            ],
                            'MTQ' => [
                                'displayName' => 'International Business Parcels Zone Sort Plus Priority'
                            ],
                            'MTS' => [
                                'displayName' => 'International Business Parcels Zone Sort Plus Economoy'
                            ],
                            'MUU' => [
                                'displayName' => 'Intlernational Business Parcels Boxable Max Sort Priority'
                            ],
                            'MUV' => [
                                'displayName' => 'International Buiness Prcls Boxable Max Sort Standard'
                            ],
                            'MUW' => [
                                'displayName' => 'International Business Parcels Boxable Max Sort Economy'
                            ],
                            'MUA' => [
                                'displayName' => 'INTL BUS PARCELS BOXABLE ZERO SORT PRI'
                            ],
                            'MUB' => [
                                'displayName' => 'INTL BUS PARCELS BOXABLE ZERO SORT ECON'
                            ],
                            'MUC' => [
                                'displayName' => 'INTL BUS PARCELS BOXABLE ZONE SORT PRI'
                            ],
                            'MUD' => [
                                'displayName' => 'INTL BUS PARCELS BOXABLE ZONE SORT ECON'
                            ],
                            'MUE' => [
                                'displayName' => 'INTL BUS PRCL TRCKD BOX ZERO SRT XTR CMP'
                            ],
                            'MUF' => [
                                'displayName' => 'INTL BUS PARCELS TRACKED BOX ZERO SORT'
                            ],
                            'MUG' => [
                                'displayName' => 'INTL BUS PARCELS TRACKED BOX ZONE SORT'
                            ],
                            'MUH' => [
                                'displayName' => 'INTL BUS PRCL TRCKD BOX ZONE SRT XTR CMP'
                            ],
                            'MUI' => [
                                'displayName' => 'INTL BUS PARCELS TRACKED ZERO SORT'
                            ],
                            'MUJ' => [
                                'displayName' => 'INTL BUS PARCEL TRACKED ZERO SRT XTR CMP'
                            ],
                            'MUK' => [
                                'displayName' => 'INTL BUS PARCEL TRACKD & SIGNED ZERO SRT'
                            ],
                            'MUL' => [
                                'displayName' => 'INT BUS PRCL TRCKD & SGND ZRO SRT XT CMP'
                            ],
                            'MUM' => [
                                'displayName' => 'INTL BUS PARCELS SIGNED ZERO SORT'
                            ],
                            'MUN' => [
                                'displayName' => 'INTL BUS PARCEL SIGNED ZERO SORT XTR CMP'
                            ],
                            'MUO' => [
                                'displayName' => 'INTL BUS MAIL TRACKED ZERO SORT'
                            ],
                            'MUP' => [
                                'displayName' => 'INTL BUS MAIL TRACKED ZERO SORT XTRA CMP'
                            ],
                            'MUQ' => [
                                'displayName' => 'INTL BUS MAIL TRACKED & SIGNED ZERO SORT'
                            ],
                            'MUR' => [
                                'displayName' => 'INT BUS MAIL TRCKD & SGND ZRO SRT XT CMP'
                            ],
                            'MUS' => [
                                'displayName' => 'INTL BUS MAIL SIGNED ZERO SORT'
                            ],
                            'MUT' => [
                                'displayName' => 'INTL BUS MAIL SIGNED ZERO SORT XTRA COMP'
                            ],
                            'OLA' => [
                                'displayName' => 'International Standard On Account'
                            ],
                            'OLS' => [
                                'displayName' => 'International Economy On Account'
                            ],
                            'OSA' => [
                                'displayName' => 'International Signed On Account (Zonal Pricing)'
                            ],
                            'OSB' => [
                                'displayName' => 'International Signed On Account Extra Compensation (Zonal Pricing)'
                            ],
                            'OTA' => [
                                'displayName' => 'International Tracked On Account (Zonal Pricing)'
                            ],
                            'OTB' => [
                                'displayName' => 'International Tracked On Account Extra Compensation (Zonal Pricing)'
                            ],
                            'OTC' => [
                                'displayName' => 'International Tracked & Signed On Account (Zonal Pricing)'
                            ],
                            'OTD' => [
                                'displayName' => 'International Tracked & Signed On Account Extra Compensation (Zonal Pricing)'
                            ],
                            'OZ1' => [
                                'displayName' => 'International Business Mail Mixed Zone Sort Priority'
                            ],
                            'OZ3' => [
                                'displayName' => 'International Business Mail Mixed Zone Sort Economy'
                            ],
                            'OZ4' => [
                                'displayName' => 'International Business Mail Mixed Zone Sort Priority Machine'
                            ],
                            'OZ6' => [
                                'displayName' => 'International Business Mail Mixed Zone Srt Economy Machine'
                            ],
                            'PK0' => [
                                'displayName' => 'Royal Mail 48 (LL) Flat Rate'
                            ],
                            'PK1' => [
                                'displayName' => 'Royal Mail 24 Standard/Signed For (Parcel - Sort8 - Flat Rate Service)'
                            ],
                            'PK2' => [
                                'displayName' => 'Royal Mail 48 Standard/Signed For (Parcel - Sort8 - Flat Rate Service)'
                            ],
                            'PK3' => [
                                'displayName' => 'Royal Mail 24 Standard/Signed For (Parcel - Sort8 - Daily Rate Service)'
                            ],
                            'PK4' => [
                                'displayName' => 'Royal Mail 48 Standard/Signed For (Parcel - Sort8 - Daily Rate Service)'
                            ],
                            'PK7' => [
                                'displayName' => 'Royal Mail 24 (Presorted) (P)'
                            ],
                            'PK8' => [
                                'displayName' => 'Royal Mail 48 (Presorted) (P)'
                            ],
                            'PK9' => [
                                'displayName' => 'Royal Mail 24 (LL) Flat Rate'
                            ],
                            'PKB' => [
                                'displayName' => 'RM24 (Presorted) (P) Annual Flat Rate'
                            ],
                            'PKD' => [
                                'displayName' => 'RM48 (Presorted) (P) Annual Flat Rate'
                            ],
                            'PKK' => [
                                'displayName' => 'RM48 (Presorted) (LL) Annual Flat Rate'
                            ],
                            'PKM' => [
                                'displayName' => 'RM24 (Presorted)(LL) Annual Flat Rate'
                            ],
                            'PPF' => [
                                'displayName' => 'Royal Mail 24/48 Standard/Signed For (Packetpost- Flat Rate Service)'
                            ],
                            'PPJ' => [
                                'displayName' => 'Parcelpost Flat Rate (Annual)'
                            ],
                            'PPS' => [
                                'displayName' => 'RM24 (LL) Annual Flat Rate'
                            ],
                            'PPT' => [
                                'displayName' => 'RM48 (LL) Annual Flat Rate'
                            ],
                            'PS0' => [
                                'displayName' => 'International Business Parcels Max Sort Economy Service'
                            ],
                            'PS7' => [
                                'displayName' => 'International Business Mail Large Letter Max Sort Priority Service'
                            ],
                            'PS8' => [
                                'displayName' => 'International Business Mail Large Letter Max Sort Economy Service'
                            ],
                            'PS9' => [
                                'displayName' => 'International Business Parcels Max Sort Priority Service'
                            ],
                            'PSB' => [
                                'displayName' => 'International Business Mail Large Letter Max Sort Standard Service'
                            ],
                            'PSC' => [
                                'displayName' => 'International Business Parcels Max Sort Standard Service'
                            ],
                            'RM0' => [
                                'displayName' => 'Royal Mail 48 (Sort8)(P) Annual Flat Rate'
                            ],
                            'RM1' => [
                                'displayName' => 'Royal Mail 24 (LL) Daily Rate'
                            ],
                            'RM2' => [
                                'displayName' => 'Royal Mail 24 (P) Daily Rate'
                            ],
                            'RM3' => [
                                'displayName' => 'Royal Mail 48 (LL) Daily Rate'
                            ],
                            'RM4' => [
                                'displayName' => 'Royal Mail 48 (P) Daily Rate'
                            ],
                            'RM5' => [
                                'displayName' => 'Royal Mail 24 (P) Annual Flat Rate'
                            ],
                            'RM6' => [
                                'displayName' => 'Royal Mail 48 (P) Annual Flat Rate'
                            ],
                            'RM7' => [
                                'displayName' => 'Royal Mail 24 (SORT8) (LL) Annual Flat Rate'
                            ],
                            'RM8' => [
                                'displayName' => 'Royal Mail 24 (SORT8) (P) Annual Flat Rate'
                            ],
                            'RM9' => [
                                'displayName' => 'Royal Mail 48 (SORT8) (LL) Annual Flat Rate'
                            ],
                            'SD1' => [
                                'displayName' => 'Special Delivery Guaranteed By 1PM (£500)'
                            ],
                            'SD2' => [
                                'displayName' => 'Special Delivery Guaranteed By 1PM (£1000)'
                            ],
                            'SD3' => [
                                'displayName' => 'Special Delivery Guaranteed By 1PM (£2500)'
                            ],
                            'SD4' => [
                                'displayName' => 'Special Delivery Guaranteed By 9AM (£50)'
                            ],
                            'SD5' => [
                                'displayName' => 'Special Delivery Guaranteed By 9AM (£1000)'
                            ],
                            'SD6' => [
                                'displayName' => 'Special Delivery Guaranteed By 9AM (£2500)'
                            ],
                            'STL' => [
                                'displayName' => 'Royal Mail 1st Class/ 2nd Class  Standard/Signed For (Letters - Daily Rate service)'
                            ],
                            'TPL' => [
                                'displayName' => 'Tracked 48 High Volume Signature/ No Signature'
                            ],
                            'TPM' => [
                                'displayName' => 'Tracked 24 High Volume Signature/ No Signature'
                            ],
                            'TPN' => [
                                'displayName' => 'Tracked 24 Signature/ No Signature'
                            ],
                            'TPS' => [
                                'displayName' => 'Tracked 48 Signature/ No Signature'
                            ],
                            'TRL' => [
                                'displayName' => 'Tracked Letter-Boxable 48 High Volume Signature'
                            ],
                            'TRM' => [
                                'displayName' => 'Tracked Letter-Boxable 24 High Volume No Signature'
                            ],
                            'TRN' => [
                                'displayName' => 'Tracked Letter-Boxable 24 No Signature'
                            ],
                            'TRS' => [
                                'displayName' => 'Tracked Letter-Boxable 48 No Signature'
                            ],
                            'TSN' => [
                                'displayName' => 'Tracked Returns 24'
                            ],
                            'TSS' => [
                                'displayName' => 'Tracked Returns 48'
                            ],
                            'WE1' => [
                                'displayName' => 'International Business Parcels Zero Sort Priority'
                            ],
                            'WE3' => [
                                'displayName' => 'International Business Parcels Zero Sort Economy'
                            ],
                            'WG1' => [
                                'displayName' => 'International Business Mail Large Letter Zero Sort Priority'
                            ],
                            'WG3' => [
                                'displayName' => 'International Business Mail Large Letter Zero Sort Economy'
                            ],
                            'WG4' => [
                                'displayName' => 'International Business Mail Large Letter Zero Sort Priority Machine'
                            ],
                            'WG6' => [
                                'displayName' => 'International Business Mail Large Letter Zero Srt Economy Machine'
                            ],
                            'WW1' => [
                                'displayName' => 'International Business Mail Mixed Zero Sort Priority'
                            ],
                            'WW3' => [
                                'displayName' => 'International Business Mail Mixed Zero Sort Economy'
                            ],
                            'WW4' => [
                                'displayName' => 'International Business Mail Mixed Zero Sort Priority Machine'
                            ],
                            'WW6' => [
                                'displayName' => 'International Business Mail Mixd Zero Sort Economy Machine'
                            ]
                        ],
                        'serviceTypes' => [
                            '1' => [
                                'description' => 'Royal Mail 24 / 1st Class',
                                'domestic' => true,
                            ],
                            '2' => [
                                'description' => 'Royal Mail 48 / 2nd Class',
                                'domestic' => true,
                            ],
                            'D' => [
                                'description' => 'Special Delivery Guaranteed',
                                'domestic' => true,
                            ],
                            'H' => [
                                'description' => 'HM Forces (BFPO)',
                                'domestic' => true,
                            ],
                            'I' => [
                                'description' => 'International',
                                'domestic' => false,
                            ],
                            'R' => [
                                'description' => 'Tracked Returns',
                                'domestic' => true,
                            ],
                            'T' => [
                                'description' => 'Royal Mail Tracked',
                                'domestic' => true,
                            ]
                        ],
                        'serviceFormats' => [
                            'domestic' => [
                                'L' => 'Letter',
                                'F' => 'Large Letter',
                                'P' => 'Parcel',
                                'N' => 'Not Applicable'
                            ],
                            'international' => [
                                'P' => 'Letter',
                                'G' => 'Large Letter',
                                'E' => 'Parcel',
                                'N' => 'Not Applicable'
                            ]
                        ],
                        'serviceAddOns' => [
                            '1' => [
                                'description' => 'Consequential Loss £1000',
                                'enhancementGroup' => 'Consequential Loss Insurance'
                            ],
                            '2' => [
                                'description' => 'Consequential Loss £2500',
                                'enhancementGroup' => 'Consequential Loss Insurance'
                            ],
                            '3' => [
                                'description' => 'Consequential Loss £5000',
                                'enhancementGroup' => 'Consequential Loss Insurance'
                            ],
                            '4' => [
                                'description' => 'Consequential Loss £7500',
                                'enhancementGroup' => 'Consequential Loss Insurance'
                            ],
                            '5' => [
                                'description' => 'Consequential Loss £10000',
                                'enhancementGroup' => 'Consequential Loss Insurance'
                            ],
                            '6' => [
                                'description' => 'Recorded',
                                'enhancementGroup' => 'Recorded Signed For Mail'
                            ],
                            '11' => [
                                'description' => 'Consequential Loss £750',
                                'enhancementGroup' => 'Consequential Loss Insurance'
                            ],
                            '12' => [
                                'description' => 'Tracked Signature'
                            ],
                            '13' => [
                                'description' => 'SMS Notification'
                            ],
                            '14' => [
                                'description' => 'E-Mail Notification'
                            ],
                            '16' => [
                                'description' => 'SMS & E-Mail Notification'
                            ],
                            '22' => [
                                'description' => 'Local Collect'
                            ],
                            '24' => [
                                'description' => 'Saturday Guaranteed'
                            ]
                        ],
                        'services' => [
                            [
                                'serviceOffering' => 'BF1',
                                'serviceTypes' => [
                                    'H'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BF2',
                                'serviceTypes' => [
                                    'H'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BF7',
                                'serviceTypes' => [
                                    'H'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BF8',
                                'serviceTypes' => [
                                    'H'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BF9',
                                'serviceTypes' => [
                                    'H'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'CRL',
                                'serviceTypes' => [
                                    '1','2'
                                ],
                                'shipmentClass' => DomesticLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'F','P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'DE1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DE3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DE4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DE6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DG1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DG3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DG4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DG6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DW1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'FS1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'FS2',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IE1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IE3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IG1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IG3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IG4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IG6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MB1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'E','N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MB2',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MB3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP0',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP5',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP7',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP8',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MP9',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTA',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTB',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTC',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTD',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTE',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTF',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTG',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTH',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTI',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTJ',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTK',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTL',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTM',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTN',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTO',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTP',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTQ',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTS',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OLA',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'E','G','N','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OLS',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'E','G','N','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OSA',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OSB',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OTA',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OTB',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OTC',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OTD',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'E','G','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OZ1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OZ3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OZ4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'OZ6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PK0',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PK1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PK2',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PK3',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'F','P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PK4',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'F','P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PK9',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PPF',
                                'serviceTypes' => [
                                    '1','2'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'PS0',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PS7',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PS8',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PS9',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PSB',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PSC',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PT1',
                                'serviceTypes' => [
                                    'R'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PT2',
                                'serviceTypes' => [
                                    'R'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PX0',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'A','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PX1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'A','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PX2',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'A','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PY1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PY2',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PY3',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PY4',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PZ4',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'A','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PZ5',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'A','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'RM0',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM2',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM3',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM4',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM5',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'F','P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM6',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'F','P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM7',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM8',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => Shipment::class,
                                'serviceFormats' => [
                                    'P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'RM9',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'F'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'SD1',
                                'serviceTypes' => [
                                    'D'
                                ],
                                'shipmentClass' => DomesticSpecialDeliveryNotApplicable::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '1','2','3','4','5','14','13','16','22','24'
                                ],
                            ],
                            [
                                'serviceOffering' => 'SD2',
                                'serviceTypes' => [
                                    'D'
                                ],
                                'shipmentClass' => DomesticSpecialDeliveryNotApplicable::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '1','2','3','4','5','14','13','16','22','24'
                                ],
                            ],
                            [
                                'serviceOffering' => 'SD3',
                                'serviceTypes' => [
                                    'D'
                                ],
                                'shipmentClass' => DomesticSpecialDeliveryNotApplicable::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '1','2','3','4','5','14','13','16','22','24'
                                ],
                            ],
                            [
                                'serviceOffering' => 'SD4',
                                'serviceTypes' => [
                                    'D'
                                ],
                                'shipmentClass' => DomesticSpecialDeliveryNotApplicable::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '1','2','3','4','5','14','13','16','22','24'
                                ],
                            ],
                            [
                                'serviceOffering' => 'SD5',
                                'serviceTypes' => [
                                    'D'
                                ],
                                'shipmentClass' => DomesticSpecialDeliveryNotApplicable::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '1','2','3','4','5','14','13','16','22','24'
                                ],
                            ],
                            [
                                'serviceOffering' => 'SD6',
                                'serviceTypes' => [
                                    'D'
                                ],
                                'shipmentClass' => DomesticSpecialDeliveryNotApplicable::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '1','2','3','4','5','14','13','16','22','24'
                                ],
                            ],
                            [
                                'serviceOffering' => 'STL',
                                'serviceTypes' => [
                                    '1','2'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'F','L','P'
                                ],
                                'serviceAddOns' => [
                                    '6'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TPL',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TPM',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TPN',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TPS',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TRL',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TRM',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TRN',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'TRS',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [
                                    '14','13','16','22'
                                ],
                            ],
                            [
                                'serviceOffering' => 'WE1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WE3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WG1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WG3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WG4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WG6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WW1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WW3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WW4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalNotApplicableShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                        ]
                    ]
                ]
            ]
        ]
    ]
];