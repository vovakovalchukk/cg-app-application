define([
    'react',
    'redux-form',
    'Common/Components/Input',
    './Ebay/ShippingService'
], function(
    React,
    ReduxForm,
    Input,
    ShippingService
) {
    "use strict";

    var Field = ReduxForm.Field;

    var EbayChannelFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                shippingMethods: {}
            };
        },
        renderDispatchTimeMax: function(field) {
            return this.renderInput('Dispatch Time Max', field);
        },
        renderShippingPrice: function(field) {
            return this.renderInput('Shipping Price', field);
        },
        renderInput: function(label, field) {
            return (
                <label>
                    <span className={"inputbox-label"}>{label}</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name={field.input.name}
                            inputType="number"
                            value={field.input.value}
                            onChange={field.input.onChange}
                        />
                    </div>
                </label>
            );
        },
        render: function() {
            return (
                <div className="ebay-channel-form-container channel-form-container">
                    <Field name="dispatchTimeMax" component={this.renderDispatchTimeMax} />
                    <ShippingService shippingServices={this.props.shippingMethods} />
                    <Field name="shippingPrice" component={this.renderShippingPrice} />
                </div>
            );
        }
    });
    return EbayChannelFormComponent;
});