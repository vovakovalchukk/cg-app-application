define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/StockModeInputs'
], function(
    React,
    FixedDataTable,
    stateUtility,
    StockModeInputs
) {
    "use strict";
    
    let StockModeCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row)
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation ) {
                //todo - remove the text here before submission
                return <span></span>
            }
         
            return (
                <span>
                    stockmode cell {row.name}
                </span>
            );
        }
    });
    
    return StockModeCell;
});
