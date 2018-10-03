import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';

class AvailableCell extends React.Component {
    constructor(props) {
        super(props);
        const {products, rowIndex} = this.props;
        this.rowData = stateUtility.getRowData(products, rowIndex);
        this.availableValue = stateUtility.getCellData(
            props.products,
            props.columnKey,
            props.rowIndex
        );
    };
    render() {
        console.log('in available render... available value:', {
            availableValue: this.availableValue,
            sku:this.rowData.sku
        });
        
        return (
            <span className={this.props.className}>
                <Input
                    name='available'
                    initialValue={this.availableValue}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, this.rowData)}
                    classNames={'u-width-120px'}
                />
            </span>
        );
    }
}

export default AvailableCell;