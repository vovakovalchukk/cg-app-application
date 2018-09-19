define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility',
    'Common/Components/SafeInput'
], function(
    React,
    FixedDataTable,
    stateUtility,
    Input
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
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row)
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation) {
                //todo - remove the text here before submission
                return <span></span>
            }
            
            return (
                <span>
                    <Input
                        name='weight'
                        initialValue={(row.details && row.details.weight) ? parseFloat(row.details.weight).toFixed(3): ''}
                        step="0.1"
                        submitCallback={this.props.actions.saveDetail.bind(this,row)}
                    />
                </span>
            );
        }
    });
    
    return WeightCell;
});
