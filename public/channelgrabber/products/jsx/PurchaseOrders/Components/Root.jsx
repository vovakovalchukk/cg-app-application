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
                        <div className="grid-table">
                            <div className="grid-table-header-row">
                                <div className="grid-table-col">Status</div>
                                <div className="grid-table-col">Date</div>
                                <div className="grid-table-col">Number</div>
                            </div>
                        {this.props.purchaseOrders.filter(this.filterPurchaseOrders).map(function (purchaseOrder) {
                            var statusClass = purchaseOrder.status.replace(" ", "_").toLowerCase();
                            return (
                                <div className="grid-table-row">
                                    <div className="grid-table-col"><span className={"status " + statusClass}>{purchaseOrder.status}</span></div>
                                    <div className="grid-table-col">{purchaseOrder.date}</div>
                                    <div className="grid-table-col">{purchaseOrder.number}</div>
                                </div>
                            );

                        })}
                        </div>
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