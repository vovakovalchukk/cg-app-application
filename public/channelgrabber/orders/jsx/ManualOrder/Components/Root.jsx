define([
    'react',
    'ManualOrder/Components/OrderForm'
], function(
    React,
    OrderForm
) {
    "use strict";

    var RootComponent = React.createClass({
        getChildContext: function() {
            return {
                carrierUtils: this.props.utilities.carrier,
                currencyUtils: this.props.utilities.currency,
                imageUtils: this.props.utilities.image
            };
        },
        render: function()
        {
            return (
                <div>
                    <OrderForm />
                </div>
            );
        }
    });

    RootComponent.childContextTypes = {
        carrierUtils: React.PropTypes.object,
        currencyUtils: React.PropTypes.object,
        imageUtils: React.PropTypes.object
    };

    return RootComponent;
});