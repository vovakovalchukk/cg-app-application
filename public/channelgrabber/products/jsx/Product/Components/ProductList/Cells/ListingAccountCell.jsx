define([
    'react',
    'Clipboard',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/ListingStatus'
], function(
    React,
    Clipboard,
    FixedDataTable,
    stateUtility,
    ListingStatus
) {
    "use strict";
    
    let ListingAccountCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        componentDidMount: function() {
            new Clipboard('div.js-' + this.getUniqueClassName(), [], 'data-copy');
        },
        getUniqueClassName: function() {
            return this.props.columnKey + '-' + this.props.rowIndex;
        },
        render() {
            let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            let listingsForAccount = getListingsForAccount(row, this.props.listingAccountId);
            if(!listingsForAccount){
                return <span/>
            }
            let mostNegativeListingStateFromListings = getMostNegativeListingStateFromListings(listingsForAccount);
            let {status} = mostNegativeListingStateFromListings;
            
            return <ListingStatus
                status={status}
            />;
        }
    });
    
    return ListingAccountCell;
    
    function getListingsForAccount(rowData, listingAccountId) {
        let {listingsPerAccount, listings} = rowData;
        let listingsIdsForAccount = listingsPerAccount[listingAccountId];
        if (!listingsIdsForAccount) {
            return;
        }
    
        return listingsIdsForAccount.map((listingId) => {
            return listings[listingId];
        });
    }
    
    function getMostNegativeListingStateFromListings(listings) {
        let listingStatusesByPriority = [
            {
                status: 'active',
                getHoverMessage: () => ('This is an active listing with available stock'),
                statusPriority: 0
            }, {
                status: 'pending',
                getHoverMessage: (listing) => ('We have recently sent a stock update to this listing, and are currently waiting for ' + $.trim(listing.channel) + ' to confirm they have received and processed the stock update'),
                statusPriority: 1
            }, {
                status: 'paused',
                getHoverMessage: () => ( 'Listing is paused due to no stock being available for sale'),
                statusPriority: 2
            }, {
                status: 'error',
                getHoverMessage: () => ( 'Listing is paused due to no stock being available for sale'),
                statusPriority: 3
            }, {
                status: 'inative',
                getHoverMessage: () => ( 'Listing is paused due to no stock being available for sale' ),
                statusPriority: 4
            }, {
                status: 'uninmported',
                getHoverMessage: () => (  'Listing is paused due to no stock being available for sale' ),
                statusPriority: 5
            },
        ];
        
        if (!listings) {
            return;
        }
        let highestPriorityStatus = listingStatusesByPriority[0];
        listings.forEach((listing) => {
            let relevantListingStatus = listingStatusesByPriority.find(status => {
                return listing.status === status.status;
            });
            if (relevantListingStatus.statusPriority > highestPriorityStatus.statusPriority) {
                highestPriorityStatus = relevantListingStatus;
            }
        });
        return highestPriorityStatus;
    }
});
