import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Input from 'Common/Components/SafeInput';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";

class WeightCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        rows: {}
    };

    render() {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rowData
        } = this.props;

        const isSimpleProduct = stateUtility.isSimpleProduct(rowData)
        const isVariation = stateUtility.isVariation(rowData);

        if (!isSimpleProduct && !isVariation) {
            return <span></span>
        }

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        return (
            <span className={this.props.className}>
                <Input
                    name='weight'
                    initialValue={(rowData.details && rowData.details.weight) ? parseFloat(rowData.details.weight) : ''}
                    step="0.1"
                    submitCallback={this.props.actions.saveDetail.bind(this, rowData)}
                    classNames={'u-width-120px'}
                    submitsPortalSettings={portalSettings}
                    width={45}
                />
            </span>
        );
    }
}

export default WeightCell;