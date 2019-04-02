import React from 'react';
import StatelessSelect from 'Common/Components/Select--stateless';
import styled from "styled-components";
import portalFactory from "../Portal/portalFactory";
import {StyledSafeSubmits} from "../Cell/StockMode";
import stateUtility from 'Product/Components/ProductList/stateUtility';

const Container = styled.div`
    display: flex;
    align-items: center;  
`;
const ValueInput = styled.input`
    width: 45px;
    color: ${props => props.disabled ? 'grey !important': 'initial'};
`;

class LowStockInputs extends React.Component {
    static defaultProps = {
        product: {},
        default: {
            toggle: false,
            value: null
        },
        portalSettingsForDropdown: {},
        lowStockThreshold: {
            toggle: {
                value: null,
                editedValue: null,
                active: false
            },
            value: {
                value: null,
                editedValue: null
            }
        }
    };

    static optionValueDefault = 'default';
    static optionValueOn = 'true';
    static optionValueOff = 'false';

    static optionNameDefault = 'Default';
    static optionNameOn = 'On';
    static optionNameOff = 'Off';

    getSelectOptionsMap = () => {
        return {
            [LowStockInputs.optionValueDefault]: this.getDefaultOption(),
            [LowStockInputs.optionValueOn]: LowStockInputs.getOnOption(),
            [LowStockInputs.optionValueOff]: LowStockInputs.getOffOption()
        }
    };

    getSelectOptions = () =>  {
        return Object.values(this.getSelectOptionsMap());
    };

    getDefaultOption = () => {
        return {
            name: LowStockInputs.optionNameDefault + '(' + (this.props.default.toggle ? LowStockInputs.optionNameOn : LowStockInputs.optionNameOff) + ')',
            value: LowStockInputs.optionValueDefault
        }
    };

    static getOnOption() {
        return {
            name: this.optionNameOn,
            value: LowStockInputs.optionValueOn
        };
    }

    static getOffOption() {
        return {
            name: this.optionNameOff,
            value: LowStockInputs.optionValueOff
        };
    }

    static getStyle() {
        return {
            widthOfDropdown: 100,
            widthOfInput: 80
        };
    }

    selectToggle = (productId) => {
        this.props.actions.selectActiveToggle(this.props.columnKey, productId);
    };

    isToggleActive = () => {
        let isCurrentActive = stateUtility.isCurrentActiveSelect(this.props.product, this.props.select, this.props.columnKey);

        if (!isCurrentActive || this.props.scroll.userScrolling || !this.props.rows.initialModifyHasOccurred) {
            return false;
        }

        return true;
    };

    getSelectedOption = () => {
        if (!this.props.lowStockThreshold.toggle) {
            return this.getDefaultOption();
        }

        return this.getSelectOptionsMap()[this.props.lowStockThreshold.toggle.editedValue];
    };

    onOptionChange = (selectedOption) => {
        this.props.actions.lowStockChange(this.props.product.id, 'lowStockThresholdToggle', selectedOption.value);
    };

    getInputValue = (selectedOption) => {
        let inputValue = this.props.lowStockThreshold.value;
        if (!inputValue) {
            return null;
        }

        if (inputValue.editedValue === null || selectedOption.value === LowStockInputs.optionValueDefault) {
            return this.props.default.value;
        }

        return inputValue.editedValue;
    };

    isValueInputDisabled = (selectedOption) => {
        return selectedOption.value !== LowStockInputs.optionValueOn;
    };

    onInputValueChange = (event) => {
        this.props.actions.lowStockChange(this.props.product.id, 'lowStockThresholdValue', event.target.value);
    };

    renderSelect = () => {
        return <div className={"c-stock-mode-input__type-select-container"}>
            <StatelessSelect
                options={this.getSelectOptions()}
                selectedOption={this.getSelectedOption()}
                styleVars={LowStockInputs.getStyle()}
                selectToggle={this.selectToggle}
                inputId={this.props.product.id}
                portalSettingsForDropdown={this.props.portalSettingsForDropdown}
                active={this.isToggleActive()}
                onOptionChange={this.onOptionChange}
            />
        </div>;
    };

    renderValueInput = () => {
        let selectedOption = this.getSelectedOption();
        return <div className={'safe-input-box'}>
            <div className={'submit-input'}>
                <ValueInput
                    className={'c-input-field'}
                    value={this.getInputValue(selectedOption)}
                    disabled={this.isValueInputDisabled(selectedOption)}
                    type={'number'}
                    onChange={this.onInputValueChange}
                />
            </div>
        </div>
    };

    renderSubmits = () => {
        let portalSettings = this.props.getPortalSettingsForSubmits;

        if (!portalSettings) {
            return null;
        }

        return portalFactory.createPortal({
            portalSettings: portalSettings,
            Component: StyledSafeSubmits,
            componentProps: {
                isEditing: this.hasTheLowStockThresholdChanged(),
                submitInput: this.submitChanges,
                cancelInput: this.cancelChanges
            }
        });
    };

    hasTheLowStockThresholdChanged = () => {
        let toggle = this.props.lowStockThreshold.toggle,
            value = this.props.lowStockThreshold.value;

        if (!toggle || !value) {
            return false;
        }

        if (toggle.value !== toggle.editedValue) {
            return true;
        }

        if (this.getSelectedOption().value === LowStockInputs.optionValueOn) {
            return value.value != (value.editedValue != '' ? value.editedValue : null);
        }

        return false;
    };

    submitChanges = () => {
        const selectedToggle = this.getSelectedOption(),
            inputValue = this.getInputValue(selectedToggle);

        this.props.actions.saveLowStockToBackend(
            this.props.product.id,
            selectedToggle.value,
            inputValue
        )
    };

    cancelChanges = () => {
        this.props.actions.lowStockReset(this.props.product.id);
    };

    render() {
        return <span>
            <Container>
                {this.renderSelect()}
                {this.renderValueInput()}
            </Container>
            {this.renderSubmits()}
        </span>
    }
}

export default LowStockInputs;