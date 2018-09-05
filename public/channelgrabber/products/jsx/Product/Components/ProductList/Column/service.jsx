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
            key: 'dummyDetailsColumn' + (index + 1),
            width: 200,
            headerText: 'dummy details col ' + (index + 1),
            fixed: false,
            tab: 'details'
        }
    });
    
    let columnService = (function() {
        return {
            generateColumns: function(accounts) {
                
                let listingsColumns = generateListingsColumnsFromAccounts(accounts);
                
                
                let generatedColumns = coreColumns.concat(listingsColumns, detailsColumns);
                
                console.log('in generateColumns generatedColumns: ', generatedColumns, ' accounts: ' , accounts);
                return generatedColumns;
            }
        }
    }());
    
    return columnService;
    
    function generateListingsColumnsFromAccounts(accounts){
        let listingColumns = Array(7).fill(0).map((column, index) => {
            return {
                key: 'dummyListingColumn' + (index + 1),
                width: 200,
                headerText: 'dummy listings col ' + (index + 1),
                fixed: false,
                tab: 'listings'
            }
        });
        return listingColumns;
    }
    
});
