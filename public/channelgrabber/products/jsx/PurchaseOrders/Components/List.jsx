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
        sortPurchaseOrders: function (purchaseOrderA, purchaseOrderB) {
            var dateA = Date.parse(purchaseOrderA.date);
            var dateB = Date.parse(purchaseOrderB.date);
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
                            var statusClass = purchaseOrder.status.replace(" ", "_").toLowerCase();
                            return (
                                <div className="row hoverable" onClick={this.onRowClick.bind(this, purchaseOrder)}>
                                    <div className="cell"><span className={"status " + statusClass}>{purchaseOrder.status}</span></div>
                                    <div className="cell">{purchaseOrder.date}</div>
                                    <div className="cell">{purchaseOrder.number}</div>
                                </div>
                            );

                        }.bind(this))}
                    </div>
                </div>
            );
        }
    });

    return ListComponent;
});