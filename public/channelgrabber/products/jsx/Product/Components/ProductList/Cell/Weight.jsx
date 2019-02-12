import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";
import SafeInputStateless from 'Common/Components/SafeInputStateless';

class WeightCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        rows: {},
        width: '',
        rowData: [],
        detail: {},
        scroll: {}
    };
    getValue = (row) => {
        let detailForId = this.props.detail['weight'].byProductId[row.id];
        if (!detailForId) {
            return row.details['weight'];
        }
        if (typeof detailForId.valueEdited === "string") {
            return detailForId.valueEdited;
        }
        if (typeof detailForId.value === "string") {
            return detailForId.value;
        }
        return row.details['weight'];
    };
    shouldRenderSubmits = () => {
        return !this.props.scroll.userScrolling;
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
        let valueForWeight = this.getValue(rowData);

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });
        let isEditing = detail['weight'].byProductId[rowData.id] ? detail['weight'].byProductId[rowData.id].isEditing : false;

        return (
            <span className={this.props.className}>
                <SafeInputStateless
                    name='weight'
                    step="0.1"
                    submitCallback={this.props.actions.saveDetail.bind(this, rowData, 'weight')}
                    cancelInput={this.props.actions.cancelInput.bind(this, rowData, 'weight')}
                    setIsEditing={this.props.actions.setIsEditing.bind(this, rowData.id, 'weight')}
                    onValueChange={this.props.actions.changeDetailValue.bind(this, rowData.id, 'weight')}
                    value={valueForWeight}
                    submitsPortalSettings={portalSettings}
                    width={45}
                    isEditing={isEditing}
                    shouldRenderSubmits={this.shouldRenderSubmits()}
                />
            </span>
        );
    }
}

export default WeightCell;