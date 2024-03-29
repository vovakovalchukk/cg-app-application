import React from 'react';
import styled from 'styled-components';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import DataTablesStockModeInputs from 'Product/Components/ProductList/Components/StockModeInputs--stateless';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";
import portalFactory from "../Portal/portalFactory";
import SafeSubmits from 'Common/Components/SafeSubmits';

const StyledSafeSubmits = styled(SafeSubmits)`
    position: absolute;
    transform: translateX(-50%);
`;

const StockModeCellContainer = styled.div`
    display: flex;
    justify-content: center;
    align-items: center;
    .selected{
        .custom-select{
            padding: 0px;
        }
    }
    height: 100%;
`;

class StockModeCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        stock: {},
        cellNode: null
    };

    state = {
        editable: false,
        stockModeOption: {
            name: '',
            value: ''
        },
        stockAmount: ''
    };

    submitInput = () => {
        const row = this.props.rowData;
        this.props.actions.saveStockModeToBackend(row);
    };

    cancelInput = () => {
        const row = this.props.rowData;
        this.props.actions.cancelStockModeEdit(row);
    };

    onStockLevelChange = (event) => {
        this.onStockPropChange('stockLevel', event);
    };

    onStockModeChange = (event) => {
        this.onStockPropChange('stockMode', event);
    };

    onStockPropChange = (propToChange, event) => {
        const row = this.props.rowData;
        let value = propToChange === 'stockMode' ? event.value : event.target.value;
        this.props.actions.changeStockMode(row, value, propToChange);
    };

    getStockModeSelectActive(product, containerElement) {
        return stateUtility.shouldShowSelect({
            product,
            select: this.props.select,
            columnKey: this.props.columnKey,
            containerElement,
            scroll: this.props.scroll,
            rows: this.props.rows
        });
    };

    selectToggle(productId) {
        this.props.actions.selectActiveToggle(this.props.columnKey, productId)
    };

    getValueForStockProp(row, stockProp) {
        let stateForStockProp = this.props.stock[stockProp];
        let stockForId = stateForStockProp.byProductId[row.id];
        if (!stockForId) {
            if (stockProp === "stockModes") {
                return row.stock.stockMode;
            }
            if (stockProp === "stockLevels") {
                return row.stock.stockLevel;
            }
        }
        if (stockForId.valueEdited) {
            return stockForId.valueEdited;
        }
        if (stockForId.value) {
            return stockForId.value
        }
        return row.stock[stockProp];
    };

    createSubmits({rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, isEditing}){
        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        let Submits = <span></span>;
        if (portalSettingsForSubmits) {
            Submits = portalFactory.createPortal({
                portalSettings: portalSettingsForSubmits,
                Component: StyledSafeSubmits,
                componentProps: {
                    isEditing,
                    submitInput: this.submitInput,
                    cancelInput: this.cancelInput
                }
            });
        }
        return Submits;
    };
    render() {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            cellNode
        } = this.props;

        const row = this.props.rowData;

        if (!row) {
            return <span />
        }

        const isSimpleProduct = stateUtility.isSimpleProduct(row);
        const isVariation = stateUtility.isVariation(row);

        let isEditing = isStockModeBeingEdited(this.props.stock, row);

        if (!row.stock || (!isSimpleProduct && !isVariation)) {
            return <span/>
        }

        let containerElement = cellNode;

        let portalSettingsParams = {
            elemType: elementTypes.STOCK_MODE_SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds,
            containerElement
        };

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings(portalSettingsParams);

        let Submits = this.createSubmits({rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, isEditing});

        let {
            toggleStockModeSelect
        } = this.props.actions;

        let valueForStockModes = this.getValueForStockProp(row, "stockModes");
        let valueForStockLevels = this.getValueForStockProp(row, "stockLevels");
        let stockLevelPlaceholder = this.props.userSettings.stockLevelDefault;

        return (
            <StockModeCellContainer className={this.props.className}>
                <DataTablesStockModeInputs
                    inputId={row.id}
                    selectActive={this.getStockModeSelectActive(row, containerElement)}
                    stockModeOptions={this.props.stock.stockModeOptions}
                    stockModeType={{
                        input: {
                            value: {
                                value: valueForStockModes
                            },
                            onChange: this.onStockModeChange
                        }
                    }}
                    stockAmount={{
                        input: {
                            value: valueForStockLevels,
                            onChange: this.onStockLevelChange
                        }
                    }}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    actions={{
                        toggleStockModeSelect
                    }}
                    stockModeSelectToggle={this.selectToggle.bind(this)}
                    stockLevelPlaceholder={stockLevelPlaceholder}
                />
                {Submits}
            </StockModeCellContainer>
        );
    }
}

export default StockModeCell;
export {StockModeCellContainer, StyledSafeSubmits};

function isStockModeBeingEdited(stock, row) {
    let rowId = row.id;
    let stockModeForId = stock.stockModes.byProductId[rowId];
    let stockLevelForId = stock.stockLevels.byProductId[rowId];
    if (!stockModeForId && !stockLevelForId) {
        return false;
    }

    let isEditingStockMode = stockModeForId && stockModeForId.valueEdited && (stockModeForId.valueEdited !== stockModeForId.value);
    let isEditingStockLevel = stockLevelForId && stockLevelForId.valueEdited && (stockLevelForId.valueEdited !== stockLevelForId.value);

    if (!isEditingStockLevel && !isEditingStockMode) {
        return false;
    }

    return true;
}