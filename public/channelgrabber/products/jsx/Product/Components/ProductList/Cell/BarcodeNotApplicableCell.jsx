import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import CheckboxStatelessComponent from 'Common/Components/Checkbox--stateless';

class BarcodeNotApplicableCell extends React.Component {
    render() {
        let rowData = this.props.rowData;

        if (!stateUtility.isSimpleProduct(rowData) && !stateUtility.isVariation(rowData)) {
            return (<span></span>);
        }

        return (
            <CheckboxStatelessComponent
                className={this.props.className}
                isSelected={this.isSelected()}
                onSelect={this.onSelect}
            />
        );
    }

    isSelected = () => {
        let rowData = this.props.rowData;

        if (rowData.id in this.props.detail['barcodeNotApplicable'].byProductId) {
            let barcodeNotApplicable = this.props.detail['barcodeNotApplicable'].byProductId[rowData.id];
            if ('valueEdited' in barcodeNotApplicable) {
                return !!barcodeNotApplicable.valueEdited;
            }
            if ('value' in barcodeNotApplicable) {
                return !!barcodeNotApplicable.value;
            }
        }
        if (typeof rowData.details === "undefined") {
            return '';
        }
        if ('barcodeNotApplicable' in rowData.details) {
            return !!rowData.details['barcodeNotApplicable'];
        }

        return false;
    };

    onSelect = () => {
        this.props.actions.changeDetailValue(this.props.rowData.id, 'barcodeNotApplicable', !this.isSelected());
        this.props.actions.saveDetail(this.props.rowData, 'barcodeNotApplicable');
    };
}

export default BarcodeNotApplicableCell;