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
        render: function() {
            return (
                <div className="ebay-channel-form-container">
                    Ebay
                </div>
            );
        }
    });
    return EbayChannelFormComponent;
});