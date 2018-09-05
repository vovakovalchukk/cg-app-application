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
            let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            console.log('this.props: ', this.props);
            let listingsForAccount = getListingsForAccount(row , this.props.listingAccountId);
            console.log('listingsForAccount: ', listingsForAccount);
    
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
        console.log('listingsPerAccount: ', listingsPerAccount);
        let listingsIdsForAccount = listingsPerAccount[listingAccountId];
        console.log('listingIdsFOrAccounts');
        if(!listingsIdsForAccount){
            return;
        }
        return listingsIdsForAccount.map((listingId)=>{
            return listings[listingId];
        });
    }
});
