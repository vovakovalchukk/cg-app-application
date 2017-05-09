define([
    'react'
], function(
    React
) {
    "use strict";

    var RootComponent = React.createClass({
        filterPurchaseOrders: function (purchaseOrder) {
            if (purchaseOrder.status === this.props.filterStatus) {
                return purchaseOrder;
            }
        },
        render: function()
        {
            return (
            <div className="purchase-orders-root">
                <div className="actions"></div>
                <div className="purchase-orders-container">
                    <div className="purchase-orders-list">
                        {this.props.purchaseOrders.filter(this.filterPurchaseOrders).map(function (purchaseOrder) {
                            return (
                                <div className="purchase-order-row">
                                    {purchaseOrder.status}
                                </div>
                            );

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