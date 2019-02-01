import React from 'react';
import StatelessSelect from 'Product/Components/ProductList/Components/Select--stateless';

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
                editedValue: null,
                active: false
            }
        }
    };

    static optionNameDefault = 'Default';
    static optionNameOn = 'On';
    static optionNameOff = 'Off';

    static getSelectOptions() {
        return [
            this.getDefaultOption(),
            this.getOnOption(),
            this.getOffOption()
        ];
    }

    static getDefaultOption() {
        return {
            name: this.optionNameDefault + '(' + (this.defaultProps.default.toggle ? this.optionNameOn : this.optionNameOff) + ')',
            value: null
        }
    }

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
            return LowStockInputs.getDefaultOption();
        }

        if (this.props.lowStockThreshold.toggle.editedValue === true) {
            return LowStockInputs.getOnOption();
        }

        return LowStockInputs.getOffOption();
    };

    onOptionChange = (productId, selectedOption) => {
        this.props.actions.lowStockChange(productId, 'lowStockThresholdToggle', selectedOption.value);
    };

    render() {
        return <StatelessSelect
            options={LowStockInputs.getSelectOptions()}
            selectedOption={this.getSelectedOption()}
            styleVars={LowStockInputs.getStyle()}
            selectToggle={this.selectToggle}
            inputId={this.props.product.id}
            portalSettingsForDropdown={this.props.portalSettingsForDropdown}
            active={this.isToggleActive()}
            onOptionChange={this.onOptionChange.bind(this, this.props.product.id)}
        />;
    }
}

export default LowStockInputs;