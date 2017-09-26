define([
    'react'
], function(
    React
) {
    "use strict";

    return React.createClass({
        filterPurchaseOrders: function (purchaseOrder) {
            if (this.props.filterStatus === 'All') {
                return purchaseOrder;
            }
            if (purchaseOrder.status === this.props.filterStatus) {
                return purchaseOrder;
            }
        },
        sortPurchaseOrders: function (purchaseOrderA, purchaseOrderB) {
            const dateA = Date.parse(purchaseOrderA.date);
            const dateB = Date.parse(purchaseOrderB.date);
            return (this.props.sortAsc ? (dateA < dateB) : (dateA > dateB));
        },
        onRowClick: function (purchaseOrder) {
            window.triggerEvent('purchaseOrderSelected', purchaseOrder);
        },
        render: function()
        {
            return (
                <div className="purchase-orders-list">
                    <div className="head">
                        <div className="row">
                            <div className="cell">Status</div>
                            <div className="cell" onClick={this.props.onDateColumnClicked}>Date <span title="Sort Column" className="sort-dir">{this.props.sortAsc ? '▼' : '▲'}</span></div>
                            <div className="cell">Number</div>
                        </div>
                    </div>
                    <div className="body">
                        {this.props.purchaseOrders.filter(this.filterPurchaseOrders).sort(this.sortPurchaseOrders).map(function (purchaseOrder) {
                            const statusClass = purchaseOrder.status.replace(" ", "_").toLowerCase();
                            return (
                                <div className="row hoverable" onClick={this.onRowClick.bind(this, purchaseOrder)}>
                                    <div className="cell"><span className={"status " + statusClass}>{purchaseOrder.status}</span></div>
                                    <div className="cell">{purchaseOrder.created}</div>
                                    <div className="cell">{purchaseOrder.externalId}</div>
                                </div>
                            );

                        }.bind(this))}
                    </div>
                </div>
            );
        }
    });
});