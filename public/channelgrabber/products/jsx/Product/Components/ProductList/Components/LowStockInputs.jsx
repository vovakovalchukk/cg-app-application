import React from 'react';
import StatelessSelect from 'Product/Components/ProductList/Components/Select--stateless';
import styled from "styled-components";
import portalFactory from "../Portal/portalFactory";
import {StyledSafeSubmits} from "../Cell/StockMode";

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

    static optionNameDefault = 'Default';
    static optionNameOn = 'On';
    static optionNameOff = 'Off';

    getSelectOptions = () =>  {
        return [
            this.getDefaultOption(),
            LowStockInputs.getOnOption(),
            LowStockInputs.getOffOption()
        ];
    };

    getDefaultOption = () => {
        return {
            name: LowStockInputs.optionNameDefault + '(' + (this.props.default.toggle ? LowStockInputs.optionNameOn : LowStockInputs.optionNameOff) + ')',
            value: null
        }
    };

    static getOnOption() {
        return {
            name: this.optionNameOn,
            value: true
        };
    }

    static getOffOption() {
        return {
            name: this.optionNameOff,
            value: false
        };
    }

    static getStyle() {
        return {
            widthOfDropdown: 100,
            widthOfInput: 80
        };
    }

    selectToggle = (productId) => {
        this.props.actions.lowStockSelectToggle(productId);
    };

    isToggleActive = () => {
        return this.props.lowStockThreshold.toggle && this.props.lowStockThreshold.toggle.active;
    };

    getSelectedOption = () => {
        if (!this.props.lowStockThreshold.toggle || this.props.lowStockThreshold.toggle.editedValue === null) {
            return this.getDefaultOption();
        }

        if (this.props.lowStockThreshold.toggle.editedValue === true) {
            return LowStockInputs.getOnOption();
        }

        return LowStockInputs.getOffOption();
    };

    onOptionChange = (selectedOption) => {
        this.props.actions.lowStockChange(this.props.product.id, 'lowStockThresholdToggle', selectedOption.value);
    };

    getInputValue = (selectedOption) => {
        let inputValue = this.props.lowStockThreshold.value;
        if (!inputValue) {
            return null;
        }

        if (inputValue.editedValue === null || selectedOption.value === null) {
            return this.props.default.value;
        }

        return inputValue.editedValue;
    };

    isValueInputDisabled = (selectedOption) => {
        return selectedOption.value !== true;
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

        if (this.getSelectedOption().value === true) {
            return value.value != value.editedValue;
        }

        return false;
    };

    submitChanges = () => {

    };

    cancelChanges = () => {

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