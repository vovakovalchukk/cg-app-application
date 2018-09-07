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
    
    let detailsColumns = Array(7).fill(0).map((column, index) => {
        return {
            key: 'dummyDetailsColumn' + (index),
            width: 200,
            headerText: 'dummy details col ' + (index),
            fixed: false,
            tab: 'details'
        }
    });
    
    let columnService = (function() {
        return {
            generateColumnSettings: function(accounts) {
                let listingsColumns = generateListingsColumnsFromAccounts(accounts);
                let generatedColumns = coreColumns.concat(listingsColumns, detailsColumns);
                return generatedColumns;
            }
        }
    }());
    
    return columnService;
    
    function generateListingsColumnsFromAccounts(accounts){
        if (typeof accounts === "string"){
            return [];
        }
        let channelSpecificColumns = Object.keys(accounts).map((accountKey, index) => {
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
