<?php

use CG\Intersoft\RoyalMail\DeliveryService\Service as DeliveryServiceService;
use CG\Intersoft\RoyalMail\Shipment as Shipment;
use CG\Intersoft\RoyalMail\Shipment\International\LetterLargeLetterParcel as InternationalLetterLargeLetterParcelShipment;
use CG\Intersoft\RoyalMail\Shipment\Domestic\LetterLargeLetterParcel as DomesticLetterLargeLetterParcelShipment;
use CG\Intersoft\RoyalMail\Shipment\International\LetterLargeLetter as InternationalLetterLargeLetterShipment;
use CG\Intersoft\RoyalMail\Shipment\Domestic\LetterLargeLetter as DomesticLetterLargeLetterShipment;
use CG\Intersoft\RoyalMail\Shipment\International\NotApplicable as InternationalNotApplicableShipment;
use CG\Intersoft\RoyalMail\Shipment\Domestic\NotApplicable as DomesticNotApplicableShipment;
use CG\Intersoft\RoyalMail\Shipment\Domestic\LargeLetterParcel as DomesticLargeLetterParcelShipment;
use CG\Intersoft\RoyalMail\Shipment\International\LargeLetterParcel as InternationalLargeLetterParcelShipment;
use CG\Intersoft\RoyalMail\Shipment\International\Parcel as InternationalParcelShipment;
use CG\Intersoft\RoyalMail\Shipment\International\LargeLetter as InternationalLargeLetterShipment;
use CG\Intersoft\RoyalMail\Shipment\Domestic\LargeLetter as DomesticLargeLetterShipment;
use CG\Intersoft\RoyalMail\Shipment\International\NotApplicableParcel as InternationalNotApplicableParcelShipment;
use CG\Intersoft\RoyalMail\Shipment\Domestic\SpecialDeliveryNotApplicable as DomesticSpecialDeliveryNotApplicable;
use CG\Courier\Intersoft\RoyalMail\DeliveryService\DisplayName as DeliveryServiceName;

return [
    'di' => [
        'instance' => [
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        'serviceOfferings' => [
                            'BF1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BF1
                            ],
                            'BF2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BF2
                            ],
                            'BF7' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BF7
                            ],
                            'BF8' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BF8
                            ],
                            'BF9' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BF9
                            ],
                            'BPL1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BPL1
                            ],
                            'BPL2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BPL2
                            ],
                            'BPR1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BPR1
                            ],
                            'BPR2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BPR2
                            ],
                            'CRL1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_CRL1
                            ],
                            'CRL2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_CRL2
                            ],
                            'DE1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DE1
                            ],
                            'DE3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DE3
                            ],
                            'DE4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DE4
                            ],
                            'DE6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DE6
                            ],
                            'DG1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DG1
                            ],
                            'DG3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DG3
                            ],
                            'DG4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DG4
                            ],
                            'DG6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DG6
                            ],
                            'FS1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_FS1
                            ],
                            'FS2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_FS2
                            ],
                            'FS7' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_FS7
                            ],
                            'FS8' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_FS8
                            ],
                            'IE1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IE1
                            ],
                            'IE3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IE3
                            ],
                            'IG1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IG1
                            ],
                            'IG3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IG3
                            ],
                            'IG4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IG4
                            ],
                            'IG6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IG6
                            ],
                            'LA1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_LA1
                            ],
                            'LA2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_LA2
                            ],
                            'LA3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_LA3
                            ],
                            'LA4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_LA4
                            ],
                            'LA5' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_LA5
                            ],
                            'LA6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_LA6
                            ],
                            'MB1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MB1
                            ],
                            'MB2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MB2
                            ],
                            'MB3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MB3
                            ],
                            'MP0' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP0
                            ],
                            'MP1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP1
                            ],
                            'MP4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP4
                            ],
                            'MP5' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP5
                            ],
                            'MP6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP6
                            ],
                            'MP7' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP7
                            ],
                            'MP8' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP8
                            ],
                            'MP9' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MP9
                            ],
                            'MPB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPB
                            ],
                            'MPF' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPF
                            ],
                            'MPG' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPG
                            ],
                            'MPH' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPH
                            ],
                            'MPI' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPI
                            ],
                            'MPJ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPJ
                            ],
                            'MPK' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPK
                            ],
                            'MPL' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPL
                            ],
                            'MPM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPM
                            ],
                            'MPN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPN
                            ],
                            'MPO' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPO
                            ],
                            'MPP' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPP
                            ],
                            'MPQ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPQ
                            ],
                            'MPR' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPR
                            ],
                            'MPT' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPT
                            ],
                            'MPU' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPU
                            ],
                            'MPV' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPV
                            ],
                            'MPW' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPW
                            ],
                            'MPX' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPX
                            ],
                            'MPY' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MPY
                            ],
                            'MTA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTA
                            ],
                            'MTB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTB
                            ],
                            'MTC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTC
                            ],
                            'MTD' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTD
                            ],
                            'MTE' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTE
                            ],
                            'MTF' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTF
                            ],
                            'MTG' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTG
                            ],
                            'MTH' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTH
                            ],
                            'MTI' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTI
                            ],
                            'MTJ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTJ
                            ],
                            'MTK' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTK
                            ],
                            'MTL' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTL
                            ],
                            'MTM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTM
                            ],
                            'MTN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTN
                            ],
                            'MTO' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTO
                            ],
                            'MTP' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTP
                            ],
                            'MTQ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTQ
                            ],
                            'MTS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTS
                            ],
                            'MUA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUA
                            ],
                            'MUB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUB
                            ],
                            'MUC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUC
                            ],
                            'MUD' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUD
                            ],
                            'MUE' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUE
                            ],
                            'MUF' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUF
                            ],
                            'MUG' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUG
                            ],
                            'MUH' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUH
                            ],
                            'MUI' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUI
                            ],
                            'MUJ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUJ
                            ],
                            'MUK' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUK
                            ],
                            'MUL' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUL
                            ],
                            'MUM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUM
                            ],
                            'MUN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUN
                            ],
                            'MUO' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUO
                            ],
                            'MUP' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUP
                            ],
                            'MUQ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUQ
                            ],
                            'MUR' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUR
                            ],
                            'MUS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUS
                            ],
                            'MUT' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUT
                            ],
                            'MUU' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUU
                            ],
                            'MUV' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUV
                            ],
                            'MUW' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MUW
                            ],
                            'OLA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OLA
                            ],
                            'OLS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OLS
                            ],
                            'OSA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OSA
                            ],
                            'OSB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OSB
                            ],
                            'OTA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OTA
                            ],
                            'OTB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OTB
                            ],
                            'OTC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OTC
                            ],
                            'OTD' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OTD
                            ],
                            'OZ1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OZ1
                            ],
                            'OZ3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OZ3
                            ],
                            'OZ4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OZ4
                            ],
                            'OZ6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_OZ6
                            ],
                            'PK0' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK0
                            ],
                            'PK1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK1
                            ],
                            'PK2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK2
                            ],
                            'PK3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK3
                            ],
                            'PK4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK4
                            ],
                            'PK7' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK7
                            ],
                            'PK8' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK8
                            ],
                            'PK9' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PK9
                            ],
                            'PKB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PKB
                            ],
                            'PKD' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PKD
                            ],
                            'PKK' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PKK
                            ],
                            'PKM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PKM
                            ],
                            'PPF' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PPF
                            ],
                            'PPJ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PPJ
                            ],
                            'PPS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PPS
                            ],
                            'PPT' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PPT
                            ],
                            'PS0' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PS0
                            ],
                            'PS7' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PS7
                            ],
                            'PS8' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PS8
                            ],
                            'PS9' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PS9
                            ],
                            'PSB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PSB
                            ],
                            'PSC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PSC
                            ],
                            'RM0' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM0
                            ],
                            'RM1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM1
                            ],
                            'RM2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM2
                            ],
                            'RM3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM3
                            ],
                            'RM4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM4
                            ],
                            'RM5' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM5
                            ],
                            'RM6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM6
                            ],
                            'RM7' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM7
                            ],
                            'RM8' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM8
                            ],
                            'RM9' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_RM9
                            ],
                            'SD1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SD1
                            ],
                            'SD2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SD2
                            ],
                            'SD3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SD3
                            ],
                            'SD4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SD4
                            ],
                            'SD5' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SD5
                            ],
                            'SD6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SD6
                            ],
                            'STL1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_STL1
                            ],
                            'STL2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_STL2
                            ],
                            'TPL' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPL
                            ],
                            'TPM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPM
                            ],
                            'TPNN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPNN
                            ],
                            'TPNS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPNS
                            ],
                            'TPSN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPSN
                            ],
                            'TPSS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPSS
                            ],
                            'TRL' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TRL
                            ],
                            'TRM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TRM
                            ],
                            'TRNN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TRNN
                            ],
                            'TRNS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TRNS
                            ],
                            'TRSN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TRSN
                            ],
                            'TRSS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TRSS
                            ],
                            'TSN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TSN
                            ],
                            'TSS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TSS
                            ],
                            'WE1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WE1
                            ],
                            'WE3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WE3
                            ],
                            'WG1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WG1
                            ],
                            'WG3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WG3
                            ],
                            'WG4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WG4
                            ],
                            'WG6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WG6
                            ],
                            'WW1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WW1
                            ],
                            'WW3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WW3
                            ],
                            'WW4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WW4
                            ],
                            'WW6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WW6
                            ],
                            'PPJ2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PPJ2
                            ],
                            'DEA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEA
                            ],
                            'DEB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEB
                            ],
                            'DEC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEC
                            ],
                            'DED' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DED
                            ],
                            'DEM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEM
                            ],
                            'DEG' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEG
                            ],
                            'DEE' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEE
                            ],
                            'DEI' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEI
                            ],
                            'DEJ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEJ
                            ],
                            'DEK' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DEK
                            ],
                            'ITR' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITR
                            ],
                            'ITLS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITLS
                            ],
                            'ITLN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITLN
                            ],
                            'ITMS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITMS
                            ],
                            'ITMN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITMN
                            ],
                            'ITCN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITCN
                            ],
                            'ITCS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITCS
                            ],
                            'ITDN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITDN
                            ],
                            'ITDS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITDS
                            ],
                            'ITEN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITEN
                            ],
                            'ITES' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITES
                            ],
                            'ITFN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITFN
                            ],
                            'ITFS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITFS
                            ],
                            'ITSN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITSN
                            ],
                            'ITSS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITSS
                            ],
                            'ITNN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITNN
                            ],
                            'ITNS' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITNS
                            ],
                            'ITA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITA
                            ],
                            'ITB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_ITB
                            ],
                            'TPC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPC
                            ],
                            'TPD' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPD
                            ],
                            'TPA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPA
                            ],
                            'TPB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TPB
                            ],
                            'SDH' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDH
                            ],
                            'SDJ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDJ
                            ],
                            'SDK' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDK
                            ],
                            'SDM' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDM
                            ],
                            'SDN' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDN
                            ],
                            'SDQ' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDQ
                            ],
                            'SDA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDA
                            ],
                            'SDB' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDB
                            ],
                            'SDC' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDC
                            ],
                            'SDE' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDE
                            ],
                            'SDF' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDF
                            ],
                            'SDG' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_SDG
                            ],
                            'WP1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WP1
                            ],
                            'WP3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WP3
                            ],
                            'WP4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WP4
                            ],
                            'WP6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_WP6
                            ],
                            'IP1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IP1
                            ],
                            'IP3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IP3
                            ],
                            'IP4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IP4
                            ],
                            'IP6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_IP6
                            ],
                            'DP1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DP1
                            ],
                            'DP3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DP3
                            ],
                            'DP4' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DP4
                            ],
                            'DP6' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DP6
                            ],
                            'PSA' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_PSA
                            ],
                            'BP1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BP1
                            ],
                            'BP2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BP2
                            ],
                            'BP3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BP3
                            ],
                            'BG1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BG1
                            ],
                            'BG2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BG2
                            ],
                            'BG3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BG3
                            ],
                            'BE1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BE1
                            ],
                            'BE2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BE2
                            ],
                            'BE3' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_BE3
                            ],
                            'TC1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TC1
                            ],
                            'TC2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_TC2
                            ],
                            'DW1' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DW1
                            ],
                            'DW2' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_DW2
                            ],
                            'MTV' => [
                                'displayName' => DeliveryServiceName::INTERSOFT_ROYAL_MAIL_MTV
                            ],
                        ],
                        'serviceTypes' => [
                            '1' => [
                                'description' => '1st Class',
                                'domestic' => true,
                            ],
                            '2' => [
                                'description' => '2nd Class',
                                'domestic' => true,
                            ],
                            'D' => [
                                'description' => 'Special Delivery Guaranteed',
                                'domestic' => true,
                            ],
                            'H' => [
                                'description' => 'HM Forces (BFPO)',
                                'domestic' => false,
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
                                'serviceOffering' => 'CRL1',
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
                                'serviceOffering' => 'CRL2',
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
                                'serviceOffering' => 'BPL1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'L','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BPL2',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'L','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BPR1',
                                'serviceTypes' => [
                                    '1'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'L','F','P'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BPR2',
                                'serviceTypes' => [
                                    '2'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'L','F','P'
                                ],
                                'serviceAddOns' => [],
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
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
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
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
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
                                'serviceOffering' => 'STL1',
                                'serviceTypes' => [
                                    '1'
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
                                'serviceOffering' => 'STL2',
                                'serviceTypes' => [
                                    '2'
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
                                'serviceOffering' => 'TPNN',
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
                                'serviceOffering' => 'TPNS',
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
                                'serviceOffering' => 'TPSN',
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
                                'serviceOffering' => 'TPSS',
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
                                'serviceOffering' => 'TRNN',
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
                                'serviceOffering' => 'TRNS',
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
                                'serviceOffering' => 'TRSN',
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
                                'serviceOffering' => 'TRSS',
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
                            [
                                'serviceOffering' => 'PPJ2',
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
                                'serviceOffering' => 'DEA',
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
                                'serviceOffering' => 'DEB',
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
                                'serviceOffering' => 'DEC',
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
                                'serviceOffering' => 'DED',
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
                                'serviceOffering' => 'DEM',
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
                                'serviceOffering' => 'DEG',
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
                                'serviceOffering' => 'DEE',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'G'
                                ],
                                'serviceAddOns' => ['12'],
                            ],
                            [
                                'serviceOffering' => 'DEI',
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
                                'serviceOffering' => 'DEJ',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
                                ],
                                'serviceAddOns' => ['12'],
                            ],
                            [
                                'serviceOffering' => 'DEK',
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
                                'serviceOffering' => 'ITR',
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
                                'serviceOffering' => 'ITLS',
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
                                'serviceOffering' => 'ITLN',
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
                                'serviceOffering' => 'ITMS',
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
                                'serviceOffering' => 'ITMN',
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
                                'serviceOffering' => 'ITCN',
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
                                'serviceOffering' => 'ITCS',
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
                                'serviceOffering' => 'ITDN',
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
                                'serviceOffering' => 'ITDS',
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
                                'serviceOffering' => 'ITEN',
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
                                'serviceOffering' => 'ITES',
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
                                'serviceOffering' => 'ITFN',
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
                                'serviceOffering' => 'ITFS',
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
                                'serviceOffering' => 'ITSN',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'ITSS',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'ITNN',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'ITNS',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'ITA',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'ITB',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'TPC',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'TPD',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'TPA',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'TPB',
                                'serviceTypes' => [
                                    'T'
                                ],
                                'shipmentClass' => DomesticLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'N'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'SDH',
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
                                'serviceOffering' => 'SDJ',
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
                                'serviceOffering' => 'SDK',
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
                                'serviceOffering' => 'SDM',
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
                                'serviceOffering' => 'SDN',
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
                                'serviceOffering' => 'SDQ',
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
                                'serviceOffering' => 'SDA',
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
                                'serviceOffering' => 'SDB',
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
                                'serviceOffering' => 'SDC',
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
                                'serviceOffering' => 'SDE',
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
                                'serviceOffering' => 'SDF',
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
                                'serviceOffering' => 'SDG',
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
                                'serviceOffering' => 'WP1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WP3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WP4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'WP6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IP1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IP3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IP4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'IP6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DP1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DP3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DP4',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'DP6',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'PSA',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BP1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BP2',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BP3',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterShipment::class,
                                'serviceFormats' => [
                                    'P', 'G'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'BG1',
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
                                'serviceOffering' => 'BG2',
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
                                'serviceOffering' => 'BG3',
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
                                'serviceOffering' => 'BE1',
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
                                'serviceOffering' => 'BE2',
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
                                'serviceOffering' => 'BE3',
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
                                'serviceOffering' => 'TC1',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'P', 'G', 'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'TC2',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'P', 'G', 'E'
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
                                'serviceOffering' => 'DW2',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalLetterLargeLetterParcelShipment::class,
                                'serviceFormats' => [
                                    'P', 'G', 'E'
                                ],
                                'serviceAddOns' => [],
                            ],
                            [
                                'serviceOffering' => 'MTV',
                                'serviceTypes' => [
                                    'I'
                                ],
                                'shipmentClass' => InternationalParcelShipment::class,
                                'serviceFormats' => [
                                    'E'
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