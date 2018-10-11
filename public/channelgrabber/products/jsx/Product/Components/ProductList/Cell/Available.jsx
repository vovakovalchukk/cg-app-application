import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';

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
        // console.log('in available this.props: ', this.props);
        
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
        let rows = document.getElementsByClassName('fixedDataTableRowLayout_rowWrapper');
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
        let rows = this.getAllVisibleNonHeaderRows();
        
        //todo remove this debug
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        if(rowData.sku==='5055614000002'){
            console.log('targetting rowIndex this.props.rowIndex: ', this.props.rowIndex+1);
        }
        
        
        let targetDomNodeForSubmits = rows[this.props.rowIndex + 1];
        return targetDomNodeForSubmits;
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