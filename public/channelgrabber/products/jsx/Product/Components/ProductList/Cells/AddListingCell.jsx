define([
    'react',
    // 'Clipboard',
    // 'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    // Clipboard,
    // FixedDataTable,
    stateUtility
) {
    "use strict";
    
    let AddListingCell = React.createClass({
        getDefaultProps: function() {
            return {
                rowData: {},
                rowIndex: null
            };
        },
        getRowData: function() {
            return stateUtility.getRowData(this.props.products, this.props.rowIndex)
        },
        isParentProduct: function(rowData) {
            return stateUtility.isParentProduct(rowData)
        },
        onAddListingClick: async function(parentProductId) {
            console.log('onAddListingClick parentProductId: ' , parentProductId);
            const {products, rowIndex} = this.props;
            const rowData = this.getRowData(products, rowIndex);
            console.log('products.variationsByParent after get: ' , products.variationsByParent);
            this.props.actions.createNewListing({
                rowData
            });
        },
        render() {
            return (
                <span
                    onClick={this.onAddListingClick}
                >
                    add listing
                </span>
            );
        }
    });
    
    return AddListingCell;
});
