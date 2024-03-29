import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";
import SafeInputStateless from 'Common/Components/SafeInputStateless';

class CostCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        rows: {},
        width: '',
        rowData: {},
        detail: {},
        scroll: {}
    };
    getUniqueInputId = () => {
        return this.props.rowData.id+'-'+ this.props.columnKey
    };
    getValue = (row) => {
        let detailForId = this.props.detail['cost'].byProductId[row.id];
        if (!detailForId) {
            return (typeof row.details === "undefined") ? "" : row.details['cost'];
        }
        if (typeof detailForId.valueEdited === "string") {
            return detailForId.valueEdited;
        }
        if (typeof detailForId.value === "string") {
            return detailForId.value;
        }
        return row.details['cost'];
    };
    shouldRenderSubmits = () => {
        return !this.props.scroll.userScrolling;
    };
    saveDetail = () => {
        this.props.actions.saveDetail(this.props.rowData, 'cost');
    };
    cancelInput = () => {
        this.props.actions.cancelInput(this.props.rowData, 'cost');
    };
    setIsEditing = (isEditing) => {
        this.props.actions.setIsEditing(this.props.rowData.id, 'cost', isEditing);
    };
    changeDetailValue = (e) => {
        this.props.actions.changeDetailValue(this.props.rowData.id, 'cost', e);
    };
    render() {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rowData,
            detail
        } = this.props;

        const isSimpleProduct = stateUtility.isSimpleProduct(rowData);
        const isVariation = stateUtility.isVariation(rowData);

        if (!isSimpleProduct && !isVariation) {
            return <span></span>
        }
        let valueForCost = this.getValue(rowData);

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });
        let isEditing = detail['cost'].byProductId[rowData.id] ? detail['cost'].byProductId[rowData.id].isEditing : false;

        return (
            <span className={this.props.className}>
                <SafeInputStateless
                    name='cost'
                    step="0.1"
                    key={this.getUniqueInputId()}
                    submitCallback={this.saveDetail}
                    cancelInput={this.cancelInput}
                    setIsEditing={this.setIsEditing}
                    onValueChange={this.changeDetailValue}
                    value={valueForCost}
                    submitsPortalSettings={portalSettings}
                    width={45}
                    isEditing={isEditing}
                    shouldRenderSubmits={this.shouldRenderSubmits()}
                />
            </span>
        );
    }
}

export default CostCell;