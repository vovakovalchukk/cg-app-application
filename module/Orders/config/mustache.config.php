<?php
use Orders\Module;
use CG_UI\Module as UiModule;

return [
    'mustache' => [
        'template' => [
            'map' => [
                'orderList' => [
                    'id' => UiModule::PUBLIC_FOLDER . 'templates/columns/id.mustache',
                    'channel' => UiModule::PUBLIC_FOLDER . 'templates/columns/channel.mustache',
                    'accountId' => UiModule::PUBLIC_FOLDER . 'templates/columns/accountId.mustache',
                    'batch' => Module::PUBLIC_FOLDER . 'template/columns/batch.mustache',
                    'billingAddressFullName' => Module::PUBLIC_FOLDER . 'template/columns/billingAddressFullName.mustache',
                    'buyerMessage' => Module::PUBLIC_FOLDER . 'template/columns/buyerMessage.mustache',
                    'giftMessage' => Module::PUBLIC_FOLDER . 'template/columns/giftMessage.mustache',
                    'custom-tag' => Module::PUBLIC_FOLDER . 'template/columns/custom-tag.mustache',
                    'dispatchDate' => Module::PUBLIC_FOLDER . 'template/columns/dispatchDate.mustache',
                    'externalId' => Module::PUBLIC_FOLDER . 'template/columns/externalId.mustache',
                    'printedDate' => Module::PUBLIC_FOLDER . 'template/columns/printedDate.mustache',
                    'purchaseDate' => Module::PUBLIC_FOLDER . 'template/columns/purchaseDate.mustache',
                    'shippingMethod' => Module::PUBLIC_FOLDER . 'template/columns/shippingMethod.mustache',
                    'status' => Module::PUBLIC_FOLDER . 'template/columns/status.mustache',
                    'tag' => Module::PUBLIC_FOLDER . 'template/columns/tag.mustache',
                    'total' => Module::PUBLIC_FOLDER . 'template/columns/total.mustache',
                    'alerts' => Module::PUBLIC_FOLDER . 'template/columns/alerts.mustache',
                ],
                'courierReview' => [
                    'buyerOrder' => Module::PUBLIC_FOLDER . 'template/courier/columns/buyerOrder.mustache',
                    'courier' => Module::PUBLIC_FOLDER . 'template/courier/columns/courier.mustache',
                    'service' => Module::PUBLIC_FOLDER . 'template/courier/columns/service.mustache',
                    'itemImage' => Module::PUBLIC_FOLDER . 'template/courier/columns/itemImage.mustache',
                    'item' => Module::PUBLIC_FOLDER . 'template/courier/columns/item.mustache',
                ],
                'courierSpecifics' => [
                    'buyerOrder' => Module::PUBLIC_FOLDER . 'template/courier/columns/buyerOrder.mustache',
                    'service' => Module::PUBLIC_FOLDER . 'template/courier/columns/service.mustache',
                    'itemImage' => Module::PUBLIC_FOLDER . 'template/courier/columns/itemImage.mustache',
                    'item' => Module::PUBLIC_FOLDER . 'template/courier/columns/item.mustache',
                    'parcels' => Module::PUBLIC_FOLDER . 'template/courier/columns/parcels.mustache',
                    'collectionDate' => Module::PUBLIC_FOLDER . 'template/courier/columns/collectionDate.mustache',
                    'weight' => Module::PUBLIC_FOLDER . 'template/courier/columns/weight.mustache',
                    'width' => Module::PUBLIC_FOLDER . 'template/courier/columns/width.mustache',
                    'height' => Module::PUBLIC_FOLDER . 'template/courier/columns/height.mustache',
                    'length' => Module::PUBLIC_FOLDER . 'template/courier/columns/length.mustache',
                    'insurance' => Module::PUBLIC_FOLDER . 'template/courier/columns/insurance.mustache',
                    'insuranceMonetary' => Module::PUBLIC_FOLDER . 'template/courier/columns/insuranceMonetary.mustache',
                    'signature' => Module::PUBLIC_FOLDER . 'template/courier/columns/signature.mustache',
                    'deliveryInstructions' => Module::PUBLIC_FOLDER . 'template/courier/columns/deliveryInstructions.mustache',
                    'itemParcelAssignment' => Module::PUBLIC_FOLDER . 'template/courier/columns/itemParcelAssignment.mustache',
                    'packageType' => Module::PUBLIC_FOLDER . 'template/courier/columns/packageType.mustache',
                    'addOns' => Module::PUBLIC_FOLDER . 'template/courier/columns/addOns.mustache',
                    'actions' => Module::PUBLIC_FOLDER . 'template/courier/columns/actions.mustache',
                    'deliveryExperience' => Module::PUBLIC_FOLDER . 'template/courier/columns/deliveryExperience.mustache',
                ],
            ],
        ],
    ],
];
