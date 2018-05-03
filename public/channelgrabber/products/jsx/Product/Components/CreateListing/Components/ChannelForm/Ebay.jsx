define([
    'react',
    'redux-form',
    'Common/Components/Input',
    './Ebay/ShippingService',
    './Ebay/VariationImagePicker'
], function(
    React,
    ReduxForm,
    Input,
    ShippingService,
    VariationImagePicker
) {
    "use strict";

    var Field = ReduxForm.Field;

    var EbayChannelFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                shippingMethods: {},
                product: {},
                variationsDataForProduct: {}
            };
        },
        renderDispatchTimeMax: function(field) {
            return this.renderInput('Dispatch Time Max', field, 'What is the longest amount of time it may take you to dispatch an item?');
        },
        renderShippingPrice: function(field) {
            return this.renderInput('Shipping Price', field, 'How much you want to charge for shipping?');
        },
        renderInput: function(label, field, tooltip) {
            return (
                <label>
                    <span className={"inputbox-label"}>{label}</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            {...field.input}
                            inputType="number"
                            title={tooltip}
                        />
                    </div>
                </label>
            );
        },
        render: function() {
            return (
                <div className="ebay-channel-form-container channel-form-container">
                    <VariationImagePicker
                        product={this.props.product}
                        variationsDataForProduct={this.props.variationsDataForProduct}
                    />
                    <Field name="dispatchTimeMax" component={this.renderDispatchTimeMax} />
                    <ShippingService shippingServices={this.props.shippingMethods} />
                    <Field name="shippingPrice" component={this.renderShippingPrice} />
                </div>
            );
        }
    });
    return EbayChannelFormComponent;
});