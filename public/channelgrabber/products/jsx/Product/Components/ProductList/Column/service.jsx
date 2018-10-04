import React from 'react';

let coreColumns = [
    {
        key: 'bulkSelect',
        width: 40,
        fixed: true,
        headerText: '',
        align: 'center'
    },
    {
        key: 'image',
        width: 100,
        fixed: true,
        headerText: 'Image',
        align: 'center'
    },
    {
        key: 'productExpand',
        width: 40,
        fixed: true,
        headerText: '',
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
        width: 160,
        fixed: true,
        headerText: 'Sku',
        align: 'left'
    },
    {
        key: 'name',
        width: 160,
        fixed: true,
        headerText: 'Name',
        align: 'left'
    },
    {
        key: 'available',
        width: 120,
        fixed: true,
        headerText: 'Available',
        align: 'center'
    }
];

let detailsColumns = [
    {
        key: 'stockMode',
        width: 240,
        headerText: 'Stock Mode',
        fixed: false,
        tab: 'details',
        align: 'center'
    }, {
        key: 'weight',
        width: 150,
        headerText: 'Weight',
        fixed: false,
        tab: 'details',
        align: 'center'
    }, {
        key: 'dimensions',
        width: 240,
        headerText: 'Dimensions',
        fixed: false,
        tab: 'details',
        align: 'center'
    }
];

let columnService = (function() {
    return {
        generateColumnSettings: function(accounts, vat) {
            let listingsColumns = generateListingsColumnsFromAccounts(accounts);
            let vatColumns = generateVatColumns(vat);
            let generatedColumns = coreColumns.concat(listingsColumns, detailsColumns, vatColumns);
            return generatedColumns;
        }
    }
}());

export default columnService;

function generateVatColumns(vat) {
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
                key: option.countryCode,
                type: 'vat',
                countryCode: option.countryCode,
                vat,
                width: 160,
                headerText: option.countryCode,
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
    let channelSpecificColumns = Object.keys(accounts).map((accountKey) => {
        let account = accounts[accountKey];
        return {
            key: 'ListingAccountCell-' + account.id,
            type: 'listingAccount',
            listingAccountId: account.id,
            width: 115,
            headerText: capitalize(account.channel),
            fixed: false,
            tab: 'listings',
            align: 'center'
        }
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

