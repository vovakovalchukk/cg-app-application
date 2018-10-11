import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';

class AvailableCell extends React.Component {
    onChange(e){
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateAvailable(rowData,'available',e.target.value);
    };
    getAllNonHeaderRows() {
        let rows = document.getElementsByClassName('fixedDataTableRowLayout_rowWrapper');
        let nonHeaderRows = [];
        for(var i =0; i<rows.length; i++){
            if(i===0){
                continue;
            }
            nonHeaderRows.push(rows[i]);
        }
        return nonHeaderRows;
    };
    getDomNodeForAddingSubmitsTo() {
        let rows = this.getAllNonHeaderRows();
        let targetDomNodeForSubmits = rows[this.props.rowIndex + 1];
        return targetDomNodeForSubmits;
    }
    render() {
        const {products, rowIndex} = this.props;
    
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);
        
        if(isParentProduct){
            return <span></span>
        }
        let availableValue = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );
    
        return (
            <span className={this.props.className + " available-cell"}>
                <Input
                    name='available'
                    initialValue={parseFloat(availableValue)}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, rowData)}
                    inputClassNames={'u-width-120px u-text-align-right'}
                    sku={rowData.sku}
                    portalSettings={{
                        domNodeForSubmits : this.getDomNodeForAddingSubmitsTo(),
                        distanceFromLeft: this.props.distanceFromLeft
                    }}
                />
            </span>
        );
    }
}

export default AvailableCell;