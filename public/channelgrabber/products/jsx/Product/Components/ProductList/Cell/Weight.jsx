import React from 'react';
import FixedDataTable from 'fixed-data-table';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Input from 'Common/Components/SafeInput';

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
            return <span></span>
        }
        
        return (
            <span className={this.props.className}>
                    <Input
                        name='weight'
                        initialValue={(row.details && row.details.weight) ? parseFloat(row.details.weight).toFixed(3) : ''}
                        step="0.1"
                        submitCallback={this.props.actions.saveDetail.bind(this, row)}
                        classNames={'u-width-120px'}
                    />
                </span>
        );
    }
});

export default WeightCell;