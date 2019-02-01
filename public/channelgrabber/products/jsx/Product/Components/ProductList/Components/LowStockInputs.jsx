import React from 'react';
import StatelessSelect from 'Product/Components/ProductList/Components/Select--stateless';
import styled from "styled-components";

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
            widthOfInput: 100
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
        return <StatelessSelect
            options={this.getSelectOptions()}
            selectedOption={this.getSelectedOption()}
            styleVars={LowStockInputs.getStyle()}
            selectToggle={this.selectToggle}
            inputId={this.props.product.id}
            portalSettingsForDropdown={this.props.portalSettingsForDropdown}
            active={this.isToggleActive()}
            onOptionChange={this.onOptionChange}
        />;
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

    render() {
        return <Container>
            {this.renderSelect()}
            {this.renderValueInput()}
        </Container>;
    }
}

export default LowStockInputs;