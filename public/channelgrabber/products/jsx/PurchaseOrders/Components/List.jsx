define([
    'react',
    'Common/Components/Select'
], function(
    React,
    Select
) {
    "use strict";

    var ListComponent = React.createClass({
        filterPurchaseOrders: function (purchaseOrder) {
            if (this.props.filterStatus === 'All') {
                return purchaseOrder;
            }
            if (purchaseOrder.status === this.props.filterStatus) {
                return purchaseOrder;
            }
        },
        render: function()
        {
            return (
                <div className="purchase-orders-list">
                    <div className="head">
                        <div className="row">
                            <div className="cell">Status</div>
                            <div className="cell">Date</div>
                            <div className="cell">Number</div>
                        </div>
                    </div>
                    <div className="body">
                        {this.props.purchaseOrders.filter(this.filterPurchaseOrders).map(function (purchaseOrder) {
                            var statusClass = purchaseOrder.status.replace(" ", "_").toLowerCase();
                            return (
                                <div className="row hoverable">
                                    <div className="cell"><span className={"status " + statusClass}>{purchaseOrder.status}</span></div>
                                    <div className="cell">{purchaseOrder.date}</div>
                                    <div className="cell">{purchaseOrder.number}</div>
                                </div>
                            );

                        })}
                    </div>
                </div>
            );
        }
    });

    return ListComponent;
});