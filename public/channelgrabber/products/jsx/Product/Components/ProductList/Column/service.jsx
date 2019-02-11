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
        width: 80,
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
        tab: 'details',
        align: 'center'
    },
    {
        key: 'weight',
        width: 80,
        headerText: 'Weight',
        fixed: false,
        tab: 'details',
        align: 'center'
    },
    {
        key: 'dimensions',
        width: 280,
        headerText: 'Dimensions',
        fixed: false,
        tab: 'details',
        align: 'center'
    }
];

let stockColumns = [
    {
        key: 'allocated',
        width: 80,
        headerText: 'Awaiting Dispatch',
        fixed: false,
        tab: 'stock',
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
        generateColumnSettings: function(accounts, vat, features) {
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
                tab('stock', stockColumns)
            ).filter(featureFilter);
        }
    }
}());

export default columnService;

function generateVatColumns(vat) {
    if(Object.keys(vat.productsVat).length === 0){
        return getNoVatColumn();
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
                tab: 'vat',
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
    Object.keys(accounts).forEach((accountKey) => {
        let account = accounts[accountKey];
        if (!account.type.includes('sales') || account.channel === 'api') {
            return;
        }
        channelSpecificColumns.push({
            key: 'ListingAccountCell-' + account.id,
            type: 'listingAccount',
            listingAccountId: account.id,
            width: 115,
            headerText: capitalize(account.channel),
            fixed: false,
            tab: 'listings',
            align: 'center'
        });
    });
    let miscListingColumns = [
        {
            key: 'addListing',
            width: 120,
            headerText: 'Add Listing',
            fixed: false,
            tab: 'listings',
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
        tab: 'vat',
        align: 'left'
    }
}