import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import constants from 'Product/Components/ProductList/Config/constants';
import elementTypes from 'Product/Components/ProductList/PortalSettings/elementTypes';
import utility from 'Product/Components/ProductList/utility';
import portalSettingsFactory from 'Product/Components/ProductList/PortalSettings/factory'

class AvailableCell extends React.Component {
    render() {
        const {
            products,
            rowIndex,
            distanceFromLeft,
            width
        } = this.props;
        
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

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeft,
            width
        });

        return (
            <span className={this.props.className + " available-cell"}>
                <Input
                    name='available'
                    initialValue={parseFloat(availableValue)}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, rowData)}
                    inputClassNames={'u-width-100pc u-text-align-right'}
                    sku={rowData.sku}
                    submitsPortalSettings={portalSettings}
                />
            </span>
        );
    }

    getArrayOfAllRenderedRows(){
        let allVisibleNonHeaderRows = this.getAllVisibleRowNodes();
        let allRows = allVisibleNonHeaderRows.map(row=>{
            let rowIndex = utility.getRowIndexFromRow(row);
//            console.log('rowIndex taken from renderedRowClasses: ', {rowIndex,
//                'classNames':row.className});
            return rowIndex
        });
        return allRows;
    }
//    createPortalSettings() {
//        if( !this.hasRowBeenRendered()){
//          console.log('not creating portal settings since there is no domNode for this.props.rowIndex',this.props.rowIndex);
//            return;
//        }
//        let portalSettings = {
//            id:this.props.rowIndex,
//            usePortal:true,
//            domNodeForSubmits: this.getDomNodeForAddingSubmitsTo(),
//            distanceFromLeft: this.props.distanceFromLeft + (this.props.width / 2),
//            SubmitWrapper: this.getWrapperForSubmits()
//        };
//        return portalSettings;
//    }
//    hasRowBeenRendered() {
//        let allRows = this.getArrayOfAllRenderedRows()
//        let hasBeenRendered = allRows.includes(this.props.rowIndex);
////        console.log('hasBeenRendered: ', {
////            hasBeenRendered,
////            allRows,
////            rowIndex: this.props.rowIndex
////        });
//        return hasBeenRendered;
//    }
    onChange(e) {
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateAvailable(rowData, 'available', e.target.value);
    };
//    getAllVisibleRowNodes() {
//        let rows = document.getElementsByClassName(constants.ROW_CLASS_PREFIX);
//        let rowNodes = [];
//        for (var i = 0; i < rows.length; i++) {
//            if ( i===rows.length) {
//                continue;
//            }
//            rowNodes.push(rows[i]);
//        }
//        return rowNodes;
//    };
//    getDomNodeForAddingSubmitsTo() {
//        let targetClass = this.getClassOfCurrentRow();
//        let targetRow = document.querySelector(targetClass);
////        if(!targetRow){
////            console.log('CANTT GET TARGET ROW!', {
////                targetRow, targetClass
////            });
////        }
//        let targetNode = targetRow.parentNode;
//        return targetNode;
//    };
//    isLastVisibleRow() {
//        let allVisibleNonHeaderRows = this.getAllVisibleRowNodes();
//
//        let lastVisibleRow = allVisibleNonHeaderRows[allVisibleNonHeaderRows.length-1];
//        let rowClassIndex = utility.getRowIndexFromRow(lastVisibleRow);
//        return this.props.rowIndex === rowClassIndex;
//    };
//    getClassOfCurrentRow() {
//        return '.' + constants.ROW_CLASS_PREFIX +'-'+ this.props.rowIndex;
//    };
//    getWrapperForSubmits() {
//        let wrapperStyle = {
//            background: 'white',
//            width: '60px',
//            height: '30px',
//            border: 'solid blue 3px',
//            'z-index': '100',
//            position: 'absolute',
//            top: '15px',
//            left: this.props.distanceFromLeft + (this.props.width / 2) + 'px',
//            transform: 'translateX(-50%)'
//        };
//        return ({children}) => (
//            <div style={wrapperStyle} className={'this-is-the-dummy'}>
//                {children}
//            </div>
//        );
//    };
}

export default AvailableCell;