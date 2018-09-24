define([
    'react'
], function(
    React
) {
    "use strict";
    
    let coreColumns = [
        {
            key: 'image',
            width: 100,
            fixed: true,
            headerText: 'Image'
        },
        {
            key: 'productExpand',
            width: 30,
            fixed: true,
            headerText: ''
        },
        {
            key: 'link',
            width: 50,
            fixed: true,
            headerText: 'Link'
        },
        {
            key: 'sku',
            width: 160,
            fixed: true,
            headerText: 'Sku'
        },
        {
            key: 'name',
            width: 160,
            fixed: true,
            headerText: 'Name'
        },
        {
            key: 'available',
            width: 80,
            fixed: true,
            headerText: 'Available'
        }
    ];
    
    let detailsColumns = [
        {
            key: 'stockMode',
            width: 240,
            headerText: 'Stock Mode',
            fixed: false,
            tab: 'details'
        },{
            key: 'weight',
            width: 150,
            headerText: 'Weight',
            fixed: false,
            tab: 'details'
        },{
            key: 'dimensions',
            width: 240,
            headerText: 'Dimensions',
            fixed: false,
            tab: 'details'
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
    
    return columnService;
    
    function generateVatColumns(vat){
        let vatColumns=[];
        return Object.keys(vat.vatRates).map(countryCode=>{
            let options = vat.vatRates[countryCode];
            
            for(let key in options){
                let option = options[key];
                let columnForCountryExists = !!vatColumns.find(column=>{
                    return column.countryCode === option.countryCode;
                });
    
                if(columnForCountryExists){
                    return;
                }
                return {
                    key: option.countryCode,
                    type: 'vat',
                    countryCode: option.countryCode,
                    vat,
                    width: 100,
                    headerText: option.countryCode,
                    fixed: false,
                    tab: 'vat'
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
                tab: 'listings'
            }
        });
        
        let miscListingColumns = [
            {
                key: 'addListing',
                width: 120,
                headerText: 'Add Listing',
                fixed: false,
                tab: 'listings'
            }
        ];
        
        let listingColumns = channelSpecificColumns.concat(miscListingColumns);
        return listingColumns;
    }
    
    function capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }
});
