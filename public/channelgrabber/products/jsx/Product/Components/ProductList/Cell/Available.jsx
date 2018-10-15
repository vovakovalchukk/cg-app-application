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

        let allRenderedRows = this.getArrayOfAllRenderedRows()
        console.log('allRenderedRows: ', allRenderedRows);

        return (
            <span className={this.props.className + " available-cell"}>
                <Input
                    name='available'
                    initialValue={parseFloat(availableValue)}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, rowData)}
                    inputClassNames={'u-width-100pc u-text-align-right'}
                    sku={rowData.sku}
                    submitsPortalSettings={this.createPortalSettings()}
                />

            </span>
        );

    }

    getDistanceFromTop(){
        let rowNode = this.getCurrentRowNode();
        console.log('rowNode: ', rowNode);
        console.log('style of node rowNode.style: ', rowNode.style);
        let translateValue =  rowNode.style.transform;
        let heightValue = translateValue.split(',')[1].trim();
        console.log('heightValue: ', heightValue);
        return heightValue;
    }

    getArrayOfAllRenderedRows(){
        let allVisibleNonHeaderRows = this.getAllVisibleNonHeaderRows();
        let allRows = allVisibleNonHeaderRows.map(row=>{
            let rowIndex = this.getRowIndexFromRenderedRowClasses(row.className);
            return rowIndex
        });
        return allRows;
    }

    createPortalSettings() {
        if(this.isLastVisibleRow() || !this.getArrayOfAllRenderedRows().includes(this.props.rowIndex+1)){
            console.log('not creating portal settings since there is not domNode');
            return;
        }
        return {
            id:this.props.rowIndex,
            usePortal:true,
            domNodeForSubmits: this.getDomNodeForAddingSubmitsTo(),
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

    getCurrentRowNode(){
        let targetClass = this.getClassOfCurrentRow();
        console.log('targetClass: ', targetClass);
        let targetRow = document.querySelector(targetClass);

        let targetNode = targetRow.parentNode;
        return targetNode;
    }

    getDomNodeForAddingSubmitsTo() {
        let targetNode = document.querySelector('.fixedDataTableLayout_rowsContainer');
        return targetNode
    };
    
    isLastVisibleRow() {
        let allVisibleNonHeaderRows = this.getAllVisibleNonHeaderRows();

        let lastVisibleRow = allVisibleNonHeaderRows[allVisibleNonHeaderRows.length-1];
        let lastVisibleRowClasses = lastVisibleRow.className;
        let rowClassIndex = this.getRowIndexFromRenderedRowClasses(lastVisibleRowClasses);

        if(this.props.rowIndex === rowClassIndex ){
            console.log('is last row....');
            console.log('rowClassIndex: ', rowClassIndex);
            console.log('this.props.rowIndex: ', this.props.rowIndex);
        }
        return this.props.rowIndex === rowClassIndex;
    }

    getRowIndexFromRenderedRowClasses(lastVisibleRowClasses) {
        let classArray = lastVisibleRowClasses.split(' ');

        let rowClass = classArray.find(classStr => classStr.indexOf('js-row-') > -1);
        let rowClassSplitByHyphens = rowClass.split('-');
        let rowClassIndex = parseInt(rowClassSplitByHyphens[rowClassSplitByHyphens.length - 1]);
        return rowClassIndex;
    }

    getClassOfCurrentRow() {
        return '.' + constants.ROW_CLASS_PREFIX +'-'+ (this.props.rowIndex);
    };
    
    getWrapperForSubmits() {
        let wrapperStyle = {
            background: 'white',
            width: '60px',
            height: '30px',
            border: 'solid blue 3px',
            'z-index': '100',
            position: 'absolute',
            top: this.getDistanceFromTop(),
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