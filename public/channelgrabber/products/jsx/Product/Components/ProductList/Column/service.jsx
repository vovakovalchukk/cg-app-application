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
    }
];

let columnService = (function() {
    return {
        generateColumnSettings: function(accounts, vat) {
            let listingsColumns = generateListingsColumnsFromAccounts(accounts);
            let vatColumns = generateVatColumns(vat);
            let generatedColumns = coreColumns.concat(listingsColumns, detailsColumns, vatColumns, stockColumns);
            return generatedColumns;
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

    let listingColumns = channelSpecificColumns.concat(miscListingColumns);
    return listingColumns;
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