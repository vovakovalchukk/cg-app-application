define([
    'react',
    'redux-form',
    'Common/Components/Input'
], function(
    React,
    ReduxForm,
    Input
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
            return (
                <label>
                    <span className={"inputbox-label"}>Dispatch Time Max</span>
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
                <div className="ebay-channel-form-container">
                    <Field name="dispatchTimeMax" component={this.renderDispatchTimeMax} />
                </div>
            );
        }
    });
    return EbayChannelFormComponent;
});