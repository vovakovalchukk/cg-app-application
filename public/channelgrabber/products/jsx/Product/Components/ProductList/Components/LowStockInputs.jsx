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
        stock: {}
    };

    static optionDefault = 'default';
    static optionOn = 'on';
    static optionOff = 'off';

    static optionNameDefault = 'Default';
    static optionNameOn = 'On';
    static optionNameOff = 'Off';

    static getSelectOptions() {
        return [
            {
                name: this.optionNameDefault + '(' + (this.defaultProps.default.toggle ? this.optionNameOn : this.optionNameOff) + ')',
                value: this.optionDefault
            },
            {
                name: this.optionNameOn,
                value: this.optionOn
            },
            {
                name: this.optionNameOff,
                value: this.optionOff
            }
        ];
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

    render() {
        return <StatelessSelect
            options={LowStockInputs.getSelectOptions()}
            styleVars={LowStockInputs.getStyle()}
            selectToggle={this.selectToggle}
            inputId={this.props.product.id}
            portalSettingsForDropdown={this.props.portalSettingsForDropdown}
            active={this.isToggleActive()}
        />;
    }
}

export default LowStockInputs;