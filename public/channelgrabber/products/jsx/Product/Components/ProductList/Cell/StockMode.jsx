import React from 'react';
import styled from 'styled-components';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import StockModeInputs from 'Product/Components/StockModeInputs';
import constants from 'Product/Components/ProductList/Config/constants';
import elementTypes from "../PortalSettings/elementTypes";
import portalSettingsFactory from "../PortalSettings/factory";

const StyledStockModeInputs = styled(StockModeInputs)`
    display:flex;
    justify-content:center;
    align-items:center;
    .selected{
        .custom-select{
            padding:0px;
        }
    }
`;
const ButtonsContainer = styled.div`
    position:absolute;
    top:40px;
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
        
        let editStatus = getEditStatus(this.props.stock.stockModeEdits, row);
        const shouldDisplaySaveCancelBox = editStatus === constants.STOCK_MODE_EDITING_STATUSES.editing;
        
        if (!row.stock || (!isSimpleProduct && !isVariation)) {
            return <span/>
        }

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.STOCK_MODE_SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        });

        // todo --- apply this to the ButtonsContainer
        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        });

        return (
            <div className={this.props.className}>
                <StyledStockModeInputs
                    onChange={this.onStockModeChange}
                    stockModeOptions={this.props.stock.stockModeOptions}
                    stockModeType={{
                        input: {
                            value: {
                                value: row.stock.stockMode
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
                />
                <ButtonsContainer className={"safe-input-box"}>
                    <div className={"submit-input"}>
                        <div className={"submit-cancel " + (shouldDisplaySaveCancelBox ? "active" : "")}>
                            <div className="button-input" onClick={this.submitInput}><span
                                className="submit"></span></div>
                            <div className="button-input" onClick={this.cancelInput}><span
                                className="cancel"></span></div>
                        </div>
                    </div>
                </ButtonsContainer>
            </div>
        );
    }
}

export default StockModeCell;

function getEditStatus(stockModeEdits, row) {
    let editStatus = '';
    if (stockModeEdits <= 0) {
        return editStatus
    }
    let matchedEdit = stockModeEdits.find(edit => {
        return edit.productId === row.id
    });
    if (matchedEdit) {
        editStatus = matchedEdit.status;
    }
    return editStatus
}

