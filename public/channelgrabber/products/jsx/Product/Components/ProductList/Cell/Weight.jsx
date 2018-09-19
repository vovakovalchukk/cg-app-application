define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    FixedDataTable,
    stateUtility
) {
    "use strict";
    
    let WeightCell = React.createClass({
        getDefaultProps: function() {
            return {
                products: {},
                rowIndex: null
            };
        },
        getInitialState: function() {
            return {};
        },
        render() {
            console.log('in WeightCell with this.props:  ', this.props);
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row)
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation) {
                //todo - remove the text here before submission
                return <span></span>
            }
            
            //todo - change the input values to reflect what is coming back from the store
            return (
                <span>
                    in weight cell
                </span>
            );
        }
    });
    
    return WeightCell;
});
