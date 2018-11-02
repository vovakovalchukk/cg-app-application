import React from 'react';
import styled from 'styled-components';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import DataTablesStockModeInputs from 'Product/Components/ProductList/Components/StockModeInputs--stateless';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";
import portalFactory from "../Portal/portalFactory";
import SafeSubmits from 'Common/Components/SafeSubmits';

const StyledDataTablesStockModeInputs = styled(DataTablesStockModeInputs)`
    display:flex;
    justify-content:center;
    align-items:center;
    .selected{
        .custom-select{
            padding:0px;
        }
    }
`;

const StyledSafeSubmits = styled(SafeSubmits)`
    position:absolute;
    transform: translateX(-50%);
`;

class StockModeCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        stock: {}
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
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        this.props.actions.saveStockModeToBackend(row);
    };

    cancelInput = () => {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        this.props.actions.cancelStockModeEdit(row);
    };

    onStockLevelChange = (event) => {
        this.onStockPropChange('stockLevel', event);
    };

    onStockModeChange = (event) => {
        this.onStockPropChange('stockMode', event);
    };

    onStockPropChange = (propToChange, event) => {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        let value = propToChange === 'stockMode' ? event.value : event.target.value;
        this.props.actions.changeStockMode(row, value, propToChange);
    };

    getStockModeSelectActive(row) {
        if (!this.props.stock.stockModes.byProductId[row.id]) {
            return false;
        }
        return this.props.stock.stockModes.byProductId[row.id].active;
    };

    selectToggle(productId) {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        this.props.actions.toggleStockModeSelect(productId, row);
    };

    getValueForStockMode(row) {
        let stockModes = this.props.stock.stockModes;
        let stockModeForId = stockModes.byProductId[row.id];
        if (!stockModeForId) {
            return row.stock.stockMode;
        }
        if(stockModeForId.valueEdited){
            return stockModeForId.valueEdited;
        }
        if(stockModeForId.value){
            return stockModeForId.value
        }
        return row.stock.stockMode;
    };

    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        } = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isSimpleProduct = stateUtility.isSimpleProduct(row);
        const isVariation = stateUtility.isVariation(row);

        let isEditing = isStockModeBeingEdited(this.props.stock, row.id);

        if (!row.stock || (!isSimpleProduct && !isVariation)) {
            return <span/>
        }

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.STOCK_MODE_SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        });

        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        });

        let PortalledSubmits = <span></span>;
        if (portalSettingsForSubmits) {
            PortalledSubmits = portalFactory.createPortal({
                portalSettings: portalSettingsForSubmits,
                Component: StyledSafeSubmits,
                componentProps: {
                    isEditing,
                    submitInput: this.submitInput,
                    cancelInput: this.cancelInput
                }
            });
        }

        let {
            toggleStockModeSelect
        } = this.props.actions;

        return (
            <div className={this.props.className}>
                <StyledDataTablesStockModeInputs
                    inputId={row.id}
                    selectActive={this.getStockModeSelectActive(row)}
                    stockModeOptions={this.props.stock.stockModeOptions}
                    stockModeType={{
                        input: {
                            value: {
                                //todo - prioritise valueEdited from stock
                                value: this.getValueForStockMode(row)
                            },
                            onChange: this.onStockModeChange
                        }
                    }}
                    stockAmount={{
                        input: {
                            value: row.stock.stockLevel,
                            onChange: this.onStockLevelChange
                        }
                    }}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    actions={{
                        toggleStockModeSelect
                    }}
                    stockModeSelectToggle={this.selectToggle.bind(this)}
                />
                {PortalledSubmits}
            </div>
        );
    }
}

export default StockModeCell;

function isStockModeBeingEdited(stock, rowId) {
    let stockModeForId = stock.stockModes.byProductId[rowId];
    let stockLevelForId = stock.stockLevels.byProductId[rowId];
    if (!stockModeForId && !stockLevelForId) {
        return false;
    }
    if (stockModeForId && !stockModeForId.valueEdited) {
        return false;
    }
    if (stockLevelForId && !stockLevelForId.valueEdited) {
        return false;
    }
    return true;
}