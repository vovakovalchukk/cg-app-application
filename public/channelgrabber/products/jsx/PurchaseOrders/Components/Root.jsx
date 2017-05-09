define([
    'react'
], function(
    React
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function () {
            return {
                purchaseOrders: [{status: 'hey'}, {status: 'you'}]
            }
        },
        render: function()
        {
            return (
            <div className="purchase-orders-root">
                <div className="actions"></div>
                <div className="purchase-orders-container">
                    <div className="purchase-orders-list">
                        {this.state.purchaseOrders.map(function (purchaseOrder) {
                            return <div>{purchaseOrder.status}</div>;

                        })}
                    </div>
                    <div className="purchase-orders-editor">

                    </div>
                </div>
            </div>
            );
        }
    });

    return RootComponent;
});