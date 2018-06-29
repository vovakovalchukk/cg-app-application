define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'Common/Components/RefreshIcon'
], function(
    React,
    ReduxForm,
    Select,
    RefreshIcon
) {
    "use strict";

    var Field = ReduxForm.Field;

    var EbayAccountPolicy = React.createClass({
        getDefaultProps: function() {
            return {
                returnPolicies: {},
                paymentPolicies: {},
                shippingPolicies: {},
                accountId: null,
                refreshAccountPolicies: () => {},
                disabled: false
            };
        },
        renderReturnPolicyField: function () {
            return <Field
                name="paymentPolicy"
                component={this.renderSelect}
                disabled={this.props.disabled}
                options={this.props.paymentPolicies}
                displayTitle="Payment Policy"
            />;
        },
        renderShippingPolicyField: function () {
            return <Field
                name="returnPolicy"
                component={this.renderSelect}
                disabled={this.props.disabled}
                options={this.props.returnPolicies}
                displayTitle="Return Policy"
            />;
        },
        renderPaymentPolicyField: function () {
            return <Field
                name="shippingPolicy"
                component={this.renderSelect}
                disabled={this.props.disabled}
                options={this.props.shippingPolicies}
                displayTitle="Shipping Policy"
            />;
        },
        renderSelect: function(field) {
            var selectedOption = this.findSelectedOptionFromValue(field.input.value, field.options);
            return <label>
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        options={field.options}
                        autoSelectFirst={false}
                        onOptionChange={this.onOptionChange.bind(this, field.input)}
                        selectedOption={selectedOption}
                        disabled={field.disabled}
                    />
                </div>
                <RefreshIcon
                    onClick={this.refreshAccountPolicies}
                    disabled={field.disabled}
                />
            </label>;
        },
        findSelectedOptionFromValue: function(selectedValue, options) {
            for (var key in options) {
                if (options[key].value == selectedValue) {
                    return options[key];
                }
            }
            return null;
        },
        onOptionChange: function(input, selectedOption) {
            input.onChange(selectedOption.value);
        },
        refreshAccountPolicies: function () {
            this.props.refreshAccountPolicies(this.props.accountId);
        },
        render: function() {
            return <span>
                {this.renderReturnPolicyField()}
                {this.renderShippingPolicyField()}
                {this.renderPaymentPolicyField()}
            </span>;
        }
    });
    return EbayAccountPolicy;
});