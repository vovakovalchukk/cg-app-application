import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";
import SafeInputStateless from 'Common/Components/SafeInputStateless';

class BarcodeCell extends React.Component {
    render() {
        let {rowData, columnKey} = this.props;

        if (!stateUtility.isSimpleProduct(rowData) && !stateUtility.isVariation(rowData)) {
            return (<span></span>);
        }

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex: this.props.rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: this.props.distanceFromLeftSideOfTableToStartOfCell,
            width: this.props.width,
            allRows: this.props.rows.allIds
        });

        let isEditing = false;
        if (this.props.detail[columnKey].byProductId[rowData.id]) {
            isEditing = this.props.detail[columnKey].byProductId[rowData.id].isEditing;
        }

        return (
            <span className={this.props.className}>
                <SafeInputStateless
                    name={columnKey}
                    type='text'
                    inputClassNames='no-max-width'
                    key={this.getUniqueInputId()}
                    submitCallback={this.saveDetail}
                    cancelInput={this.cancelInput}
                    setIsEditing={this.setIsEditing}
                    onValueChange={this.changeDetailValue}
                    value={this.getValue()}
                    submitsPortalSettings={portalSettings}
                    width={130}
                    isEditing={isEditing}
                    shouldRenderSubmits={this.shouldRenderSubmits}
                />
            </span>
        );
    }

    getUniqueInputId = () => {
        return this.props.rowData.id + '-' + this.props.columnKey;
    };

    saveDetail = () => {
        this.props.actions.saveDetail(this.props.rowData, this.props.columnKey);
    };

    cancelInput = () => {
        this.props.actions.cancelInput(this.props.rowData, this.props.columnKey);
    };

    setIsEditing = (isEditing) => {
        this.props.actions.setIsEditing(this.props.rowData.id, this.props.columnKey, isEditing);
    };

    changeDetailValue = (event) => {
        this.props.actions.changeDetailValue(this.props.rowData.id, this.props.columnKey, event);
    };

    getValue = () => {
        let {rowData, columnKey} = this.props;

        if (rowData.id in this.props.detail[columnKey].byProductId) {
            let byProductId = this.props.detail[columnKey].byProductId[rowData.id];
            if (typeof byProductId.valueEdited === 'string') {
                return byProductId.valueEdited;
            }
            if (typeof byProductId.value === 'string') {
                return byProductId.value;
            }
        }
        if (typeof rowData.details === "undefined") {
            return '';
        }
        if (columnKey in rowData.details) {
            return rowData.details[columnKey] || '';
        }

        return '';
    };

    shouldRenderSubmits = () => {
        return !this.props.scroll.userScrolling;
    };
}

export default BarcodeCell;