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
        getHoverText: function (listing) {
            var hoverText = {
                'active': 'This is an active listing with available stock',
                'pending': 'We have recently sent a stock update to this listing, and are currently waiting for '+$.trim(listing.channel)+' to confirm they have received and processed the stock update',
                'paused': 'Listing is paused due to no stock being available for sale',
                'error': 'We received an error when sending a stock update for this listing and so we are not currently able to manage the stock for this listing.',
                'inactive': 'You do not currently have this SKU listed in this location',
                'unimported': 'This listing has not yet been imported or does not exist'
            };
            return hoverText[$.trim(listing.status)];
        },
        render() {
            let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            // console.log('this.props: ', this.props);
            let listingsForAccount = getListingsForAccount(row , this.props.listingAccountId);
            
            // console.log('listingsForAccount: ', listingsForAccount);
            // var status = $.trim(listing.status);
            // var listingUrl = $.trim(listing.url);
            // return (
            //     <td title={this.getHoverText(listing)}>
            //         <a target="_blank" href={listingUrl}>
            //         <span className={"listing-status " + status}>
            //
            //         </span>
            //         </a>
            //     </td>
            // );
            return <div>sdfsdf</div>
        }
    });
    
    return ListingAccountCell;
    
    function getListingsForAccount(rowData, listingAccountId){
        let {listingsPerAccount, listings} = rowData;
        let listingsIdsForAccount = listingsPerAccount[listingAccountId];
        if(!listingsIdsForAccount){
            return;
        }
        return listingsIdsForAccount.map((listingId)=>{
            return listings[listingId];
        });
    }
});
