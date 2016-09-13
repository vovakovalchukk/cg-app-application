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
                manualOrderUtils: this.props.manualOrderUtils
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
        manualOrderUtils: React.PropTypes.object
    };

    return RootComponent;
});