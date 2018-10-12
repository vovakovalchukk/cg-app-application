import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import constants from 'Product/Components/ProductList/Config/constants';

class AvailableCell extends React.Component {
    render() {
        const {products, rowIndex} = this.props;
        
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);
        
        if (isParentProduct) {
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
                    inputClassNames={'u-width-100pc u-text-align-right'}
                    sku={rowData.sku}
                    portalSettings={this.createPortalSettings()}
                />
            </span>
        );
    }
    
    createPortalSettings() {
        return {
            usePortal:true,
            domNodeForSubmits: this.getDomNodeForAddingSubmitsTo(),
            distanceFromLeft: this.props.distanceFromLeft + (this.props.width / 2),
            SubmitWrapper: this.getWrapperForSubmits()
        };
    }
    
    onChange(e) {
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateAvailable(rowData, 'available', e.target.value);
    };
    
    getAllVisibleNonHeaderRows() {
        let rows = document.getElementsByClassName(constants.ROW_CLASS_PREFIX);
        let nonHeaderRows = [];
        for (var i = 0; i < rows.length; i++) {
            if (i === 0 || i===rows.length-1) {
                continue;
            }
            nonHeaderRows.push(rows[i]);
        }
        return nonHeaderRows;
    };
    
    getDomNodeForAddingSubmitsTo() {
        if(this.isLastVisibleRow()){
            return;
        }
        let targetClass = this.getClassOfNextRow();
        let targetRow = document.querySelector(targetClass);
        console.log('in this.props.rowIndex: ', this.props.rowIndex);
        
        let targetNode = targetRow.parentNode;
        return targetNode;
    };
    
    isLastVisibleRow() {
        let allVisibleNonHeaderRows = this.getAllVisibleNonHeaderRows();
        let lastVisibleRow = allVisibleNonHeaderRows[allVisibleNonHeaderRows.length-1];
        let lastVisibleRowClasses = lastVisibleRow.className;
        let classArray = lastVisibleRowClasses.split(' ');
        
        let rowClass = classArray.find(classStr => classStr.indexOf('js-row-')>-1);
        let rowClassSplitByHyphens = rowClass.split('-');
        let lastRowIndex = rowClassSplitByHyphens[rowClassSplitByHyphens.length-1];
        
        
        
        console.log('rowClass: ', rowClass);
        
        
        
        return this.props.rowIndex === lastRowIndex;
    }
    
    getClassOfNextRow() {
        return '.' + constants.ROW_CLASS_PREFIX +'-'+ (this.props.rowIndex + 1);
    };
    
    getWrapperForSubmits() {
        let wrapperStyle = {
            background: 'white',
            width: '60px',
            height: '30px',
            border: 'solid blue 3px',
            'z-index': '100',
            position: 'absolute',
            top: '-10px',
            left: this.props.distanceFromLeft + (this.props.width / 2) + 'px',
            transform: 'translateX(-50%)'
        };
        return ({children}) => (
            <div style={wrapperStyle} className={'this-is-the-dummy'}>
                {children}
            </div>
        );
    }
}

export default AvailableCell;