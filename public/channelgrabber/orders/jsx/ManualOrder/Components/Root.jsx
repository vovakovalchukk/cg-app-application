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
                imageBasePath: this.props.imageBasePath
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
        imageBasePath: React.PropTypes.string
    };

    return RootComponent;
});