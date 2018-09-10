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
            // if(parentProductId){
            //     console.log('getting newVariations');
            //     await this.props.actions.getVariationsByParentProductId(parentProductId);
            // }
            const {products, rowIndex} = this.props;
            const rowData = this.getRowData(products, rowIndex);
            console.log('products.variationsByParent after get: ' , products.variationsByParent);
            
            this.props.actions.createNewListing({
                parentProductId,
                rowData,
                variationsByParent: products.variationsByParent,
                //TODO --- pass all the paramters through that are needed to build the AccountSeleectionRoot
            });
        },
        render() {
            const {products, rowIndex} = this.props;
            const rowData = this.getRowData(products, rowIndex);
            
            // console.log('rowData (checking for parent: ', rowData );
            
            // issue here is that if it's a parentP
            
            let parentProductId = this.isParentProduct(rowData) ? rowData.id : rowData.parentProductId;
            
            return (
                <span
                    onClick={this.onAddListingClick.bind(this, parentProductId)}
                >
                    add listing
                </span>
            );
        }
    });
    
    return AddListingCell;
});
