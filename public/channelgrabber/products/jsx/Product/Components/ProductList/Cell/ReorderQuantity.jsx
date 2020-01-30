import React from 'react';
import stateUtility from "../stateUtility";
import LowStockInputs, {ValueInput as ValueInputContainer, Container} from "../Components/LowStockInputs";
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import {StockModeCellContainer, StyledSafeSubmits} from "./StockMode";
import portalFactory from "../Portal/portalFactory";

class ReorderQuantityCell extends React.Component {
    static defaultProps = {
        cellNode: null
    };

    renderValueInput = (product) => {
        return <div className={'safe-input-box'}>
            <div className={'submit-input'}>
                <ValueInputContainer
                    className={'c-input-field'}
                    value={this.getReorderQuantity(product)}
                    type={'number'}
                    onChange={(event) => this.props.actions.reorderQuantityChange(product.id, event.target.value)}
                />
            </div>
        </div>
    };

    renderSubmits = (product) => {
        return portalFactory.createPortal({
            portalSettings: this.getPortalSettingsForSubmits(),
            Component: StyledSafeSubmits,
            componentProps: {
                isEditing: this.hasReorderQuantityChanged(product),
                submitInput: (newValue) => {
                    this.props.actions.saveReorderQuantityToBackend(product.id, this.getReorderQuantity(product))
                },
                cancelInput: () => {
                    this.props.actions.reorderQuantityReset(product.id)
                }
            }
        });
    };

    getPortalSettingsForSubmits = () => {
        return portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex: this.props.rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: this.props.distanceFromLeftSideOfTableToStartOfCell,
            width: this.props.width,
            allRows: this.props.rows.allIds
        });
    };

    hasReorderQuantityChanged = (product) => {
        const reorderQuantity = stateUtility.getReorderQuantityForProduct(product, this.props.stock);
        return reorderQuantity.value !== reorderQuantity.editedValue;
    };

    getReorderQuantity = (product) => {
        const reorderQuantity = stateUtility.getReorderQuantityForProduct(product, this.props.stock);

        if (!reorderQuantity.value && !reorderQuantity.editedValue) {
            return this.props.userSettings.reorderQuantity;
        }

        if (!reorderQuantity.editedValue) {
            return reorderQuantity.value;
        }

        return reorderQuantity.editedValue;
    };

    render() {
        const product = stateUtility.getRowData(this.props.products, this.props.rowIndex);

        if (stateUtility.isParentProduct(product) || !product.stock) {
            return null;
        }

        return <StockModeCellContainer>
            <Container>
                {this.renderValueInput(product)}
            </Container>
            {this.renderSubmits(product)}
        </StockModeCellContainer>;
    }
}

export default ReorderQuantityCell;