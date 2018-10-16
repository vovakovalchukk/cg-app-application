import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import constants from 'Product/Components/ProductList/Config/constants';

class AvailableCell extends React.Component {
    render() {
        console.log('in render about to reorder');
        this.reOrderRows();
//
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
//      console.log('in render ov available cell ',this.props);
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

    getArrayOfAllRenderedRows(){
        let allVisibleNonHeaderRows = this.getAllVisibleRowNodes();
        let allRows = allVisibleNonHeaderRows.map(row=>{
            let rowIndex = this.getRowIndexFromRow(row);
//            console.log('rowIndex taken from renderedRowClasses: ', {rowIndex,
//                'classNames':row.className});
            return rowIndex
        });
        return allRows;
    }
    createPortalSettings() {
        if(this.isLastVisibleRow() || !this.hasRowBeenRendered()){
//          console.log('not creating portal settings since there is no domNode for this.props.rowIndex',this.props.rowIndex);
            return;
        }
        return {
            id:this.props.rowIndex,
            usePortal:true,
            domNodeForSubmits: this.getDomNodeForAddingSubmitsTo(),
            distanceFromLeft: this.props.distanceFromLeft + (this.props.width / 2),
            SubmitWrapper: this.getWrapperForSubmits()
        };
    }
    hasRowBeenRendered() {
        let allRows = this.getArrayOfAllRenderedRows()
        let hasBeenRendered = allRows.includes(this.props.rowIndex);
//        console.log('hasBeenRendered: ', {
//            hasBeenRendered,
//            allRows,
//            rowIndex: this.props.rowIndex
//        });
        return hasBeenRendered;
    }
    onChange(e) {
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateAvailable(rowData, 'available', e.target.value);
    };
    getAllVisibleRowNodes() {
        let rows = document.getElementsByClassName(constants.ROW_CLASS_PREFIX);
        let rowNodes = [];
        for (var i = 0; i < rows.length; i++) {
            if ( i===rows.length-1) {
                continue;
            }
            rowNodes.push(rows[i]);
        }
        return rowNodes;
    };
    getDomNodeForAddingSubmitsTo() {
        if(this.isLastVisibleRow()){
            return '';
        }
        let targetClass = this.getClassOfNextRow();
        let targetRow = document.querySelector(targetClass);
//        console.log('targets ',{
//            targetRow, targetClass
//        });

        let targetNode = targetRow.parentNode;
        return targetNode;
    };
    // todo - move this logic out of the Available cell
    reOrderRows(){
        let allRows = document.querySelectorAll('.js-row');
        var rowArr = [].slice.call(allRows).sort( (a, b) => {
            //todo - change this to check the classNames
            let aRowIndex = this.getRowIndexFromRow(a);
            let bRowIndex = this.getRowIndexFromRow(b);
            return aRowIndex > bRowIndex ? 1 : -1;
        });
        let parentRows = rowArr.map(row=>{
            return row.parentNode;
        });
        let rowsContainer = parentRows[0].parentNode;
        parentRows.forEach(function (row) {
            rowsContainer.appendChild(row);
        });
    };
    isLastVisibleRow() {
        let allVisibleNonHeaderRows = this.getAllVisibleRowNodes();

        let lastVisibleRow = allVisibleNonHeaderRows[allVisibleNonHeaderRows.length-1];
        let rowClassIndex = this.getRowIndexFromRow(lastVisibleRow);
        return this.props.rowIndex === rowClassIndex;
    };
    getRowIndexFromRow(visibleRow) {
        let lastVisibleRowClasses = visibleRow.className;
        let classArray = lastVisibleRowClasses.split(' ');

        let rowClass = classArray.find(classStr => classStr.indexOf('js-row-') > -1);
        let rowClassSplitByHyphens = rowClass.split('-');
        let rowClassIndex = parseInt(rowClassSplitByHyphens[rowClassSplitByHyphens.length - 1]);
        return rowClassIndex;
    };
    getClassOfNextRow() {
        return '.' + constants.ROW_CLASS_PREFIX +'-'+ (parseInt(this.props.rowIndex)+1);
    };
    getWrapperForSubmits() {
        let wrapperStyle = {
            background: 'white',
            width: '60px',
            height: '30px',
            border: 'solid blue 3px',
            'z-index': '100',
            position: 'absolute',
            top: '-15px',
            left: this.props.distanceFromLeft + (this.props.width / 2) + 'px',
            transform: 'translateX(-50%)'
        };
        return ({children}) => (
            <div style={wrapperStyle} className={'this-is-the-dummy'}>
                {children}
            </div>
        );
    };
}

export default AvailableCell;