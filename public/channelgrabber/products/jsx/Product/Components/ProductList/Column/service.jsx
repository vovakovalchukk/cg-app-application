import React from 'react';

let coreColumns = [
    {
        key: 'productExpand',
        width: 30,
        fixed: true,
        headerText: '',
        align: 'center'
    },
    {
        key: 'bulkSelect',
        width: 40,
        fixed: true,
        headerText: '',
        align: 'center'
    },
    {
        key: 'image',
        width: 50,
        fixed: true,
        headerText: 'Image',
        align: 'center'
    },
    {
        key: 'link',
        width: 50,
        fixed: true,
        headerText: 'Link',
        align: 'center'
    },
    {
        key: 'sku',
        width: 150,
        fixed: true,
        headerText: 'Sku',
        align: 'left'
    },
    {
        key: 'name',
        width: 230,
        fixed: true,
        headerText: 'Name',
        align: 'left'
    },
    {
        key: 'available',
        width: 70,
        fixed: true,
        headerText: 'Available',
        align: 'center'
    }
];

let detailsColumns = [
    {
        key: 'stockMode',
        width: 200,
        headerText: 'Stock Mode',
        fixed: false,
        align: 'center'
    },
    {
        key: 'weight',
        width: 80,
        headerText: 'Weight',
        fixed: false,
        align: 'center'
    },
    {
        key: 'dimensions',
        width: 280,
        headerText: 'Dimensions',
        fixed: false,
        align: 'center'
    }
];

let stockColumns = [
    {
        key: 'allocated',
        width: 80,
        headerText: 'Awaiting Dispatch',
        fixed: false,
        align: 'center'
    },
    {
        key: 'onPurchaseOrder',
        width: 80,
        headerText: 'Purchase Order',
        fixed: false,
        tab: 'stock',
        align: 'center'
    },
    {
        key: 'includePurchaseOrdersInAvailable',
        width: 200,
        headerText: 'Include quantity on Purchase Orders in available stock',
        fixed: false,
        tab: 'stock',
        align: 'center',
        feature: 'poStockInAvailableEnabled'
    },
    {
        key: 'lowStock',
        width: 200,
        headerText: 'Low stock threshold',
        fixed: false,
        tab: 'stock',
        align: 'center',
        feature: 'lowStockThresholdEnabled'
    }
];

let columnService = (function() {
    return {
        generateColumnSettings: function(features, accounts, vat, pickLocationNames) {
            const listingsColumns = generateListingsColumnsFromAccounts(accounts);
            const vatColumns = generateVatColumns(vat);

            let tab = (tab, columns) => {
                return columns.map((column) => {
                    column['tab'] = tab;
                    return column;
                });
            };

            let featureFilter = (column) => {
                if (!column.hasOwnProperty('feature')) {
                    return true;
                }
                if (!features.hasOwnProperty(column.feature)) {
                    return false;
                }
                return features[column.feature];
            };

            return coreColumns.concat(
                tab('listings', listingsColumns),
                tab('details', detailsColumns),
                tab('vat', vatColumns),
                tab('stock', stockColumns.concat([getPickLocationColumn(pickLocationNames)]))
            ).filter(featureFilter);
        }
    }
}());

export default columnService;

function generateVatColumns(vat) {
    if (vat.productsVat.allProductIds.length === 0) {
        return [getNoVatColumn()];
    }
    let vatColumns = [];
    return Object.keys(vat.vatRates).map(countryCode => {
        let options = vat.vatRates[countryCode];
        for (let key in options) {
            let option = options[key];
            let columnForCountryExists = !!vatColumns.find(column => {
                return column.countryCode === option.countryCode;
            });

            if (columnForCountryExists) {
                return;
            }
            return {
                key: countryCode,
                type: 'vat',
                countryCode: countryCode,
                vat,
                width: 160,
                headerText: countryCode,
                fixed: false,
                align: 'center'
            }
        }
    });
}

function generateListingsColumnsFromAccounts(accounts) {
    if (typeof accounts === "string") {
        return [];
    }

    let channelSpecificColumns = [];
    Object.keys(accounts).forEach((accountKey, index) => {
        let account = accounts[accountKey];
        if (!account.type.includes('sales') || account.channel === 'api') {
            return;
        }

        let headerText = `${account.displayName} (${capitalize(account.channel)})`;

        channelSpecificColumns.push({
            key: 'ListingAccountCell-' + account.id,
            type: 'listingAccount',
            listingAccountId: account.id,
            width: 115,
            headerText,
            fixed: false,
            align: 'center'
        });
    });
    let miscListingColumns = [
        {
            key: 'addListing',
            width: 120,
            headerText: 'Add Listing',
            fixed: false,
            align: 'center'
        }
    ];

    return channelSpecificColumns.concat(miscListingColumns);
}

function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

function getNoVatColumn() {
    return {
        key: 'noVat',
        headerText: '',
        width: 600,
        fixed: false,
        align: 'left'
    }
}

function getPickLocationColumn(pickLocationNames) {
    let selectWidth = 150;
    let padding = 5;
    return {
        key: 'pickingLocation',
        selectWidth,
        padding,
        width: (selectWidth * (pickLocationNames.length || 1)) + (padding * 2),
        headerText: 'Picking Location',
        fixed: false,
        align: 'center',
        feature: 'pickLocations'
    };
}