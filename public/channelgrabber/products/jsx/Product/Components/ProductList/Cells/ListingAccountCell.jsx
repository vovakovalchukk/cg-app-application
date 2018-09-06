define([
    'react',
    'Clipboard',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    Clipboard,
    FixedDataTable,
    stateUtility
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
            // console.log('in ListingAccountCelel this.props: ', this.props ,' can you see listingAccountId ?');
            
            
            let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            
            console.log('ListingAccountCell with row: ', row, 'this.props.listingAccountId: ', this.props.listingAccountId);
            
            
            let listingsForAccount = getListingsForAccount(row, this.props.listingAccountId);
            
            if(!listingsForAccount){
                return <div>sdfsdf</div>
            }
            let mostNegativeListingStateFromListings = getMostNegativeListingStateFromListings(listingsForAccount);
            // console.log('mostNegativeListingStateFromLIstings: ', mostNegativeListingStateFromListings);
            let {status} = mostNegativeListingStateFromListings;
            return (<td>
                <a target="_blank" >
                    <span className={"listing-status " + status}>
                        {status}
                    </span>
                </a>
            </td>);
        }
    });
    
    return ListingAccountCell;
    
    function getListingsForAccount(rowData, listingAccountId) {
        
        
        let {listingsPerAccount, listings} = rowData;
        let listingsIdsForAccount = listingsPerAccount[listingAccountId];
        if (!listingsIdsForAccount) {
            return;
        }
        console.log('in getListingssForACccount rowData: ' , rowData , ' listingsPerAccount[listingAccountId]: ' , listingsPerAccount[listingAccountId],  ' listingAccountId:', listingAccountId);
    
        return listingsIdsForAccount.map((listingId) => {
            return listings[listingId];
        });
    }
    
    function getMostNegativeListingStateFromListings(listings) {
        // console.log('in getMostNegativeListingStateFromListings listings:', listings);
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
                // console.log('relevantListingStatus.statusPriority: ', relevantListingStatus.statusPriority);
                // console.log('highestPriorityStatus.statusPriority: ', highestPriorityStatus.statusPriority);
                //
                //
                //
                // console.log('setting a higher priorty');
                highestPriorityStatus = relevantListingStatus;
            }
            // console.log('not setting a higher priortiy');
        });
        return highestPriorityStatus;
    }
});
