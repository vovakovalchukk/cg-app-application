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
            return {
                actions: {},
                rowIndex: null,
                products: {},
                listingAccountId: null
            };
        },
        getInitialState: function() {
            return {};
        },
        onAddListingClick: async function() {
            const {products, rowIndex} = this.props;
            const rowData = stateUtility.getRowData(products, rowIndex);
            this.props.actions.createNewListing({
                rowData
            });
        },
        render() {
            let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            let listingsForAccount = getListingsForAccount(row, this.props.listingAccountId);
            let mostNegativeListingStateFromListings = getMostNegativeListingStateFromListings(listingsForAccount);
            let {status} = mostNegativeListingStateFromListings;
            
            return <ListingStatus
                status={status}
                onAddListingClick={this.onAddListingClick}
                className={this.props.className}
            />;
        }
    });
    
    return ListingAccountCell;
    
    function getListingsForAccount(rowData, listingAccountId) {
        let {listingsPerAccount, listings} = rowData;
        if(!listingsPerAccount){
            return;
        }
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
                getHoverMessage: () => ('Listing is paused due to no stock being available for sale'),
                statusPriority: 2
            }, {
                status: 'error',
                getHoverMessage: () => ('Listing is paused due to no stock being available for sale'),
                statusPriority: 3
            }, {
                status: 'inactive',
                getHoverMessage: () => ('Listing is paused due to no stock being available for sale'),
                statusPriority: 4
            }, {
                status: 'uninmported',
                getHoverMessage: () => ('Listing is paused due to no stock being available for sale'),
                statusPriority: 5
            },
        ];
        if (!listings) {
            return listingStatusesByPriority.find(status => (status.status === 'inactive'));
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
