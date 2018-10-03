import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';

class AvailableCell extends React.Component {
    onChange(e){
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateAvailable(rowData,'available',e.target.value);
    };
    render() {
        const {products, rowIndex} = this.props;
    
        let rowData = stateUtility.getRowData(products, rowIndex);
        let availableValue = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );
        
        return (
            <span className={this.props.className}>
                <Input
                    name='available'
                    initialValue={parseFloat(availableValue)}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, rowData)}
                    classNames={'u-width-120px'}
                    sku={rowData.sku}
                />
            </span>
        );
    }
}

export default AvailableCell;