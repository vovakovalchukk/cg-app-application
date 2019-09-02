import React from 'react';
    

    export default class extends React.Component {
        filterPurchaseOrders = (purchaseOrder) => {
            if (this.props.filterStatus === 'All') {
                return purchaseOrder;
            }
            if (purchaseOrder.status === this.props.filterStatus) {
                return purchaseOrder;
            }
        };

        sortPurchaseOrders = (purchaseOrderA, purchaseOrderB) => {
            const dateA = Date.parse(purchaseOrderA.date);
            const dateB = Date.parse(purchaseOrderB.date);
            return (this.props.sortAsc ? (dateA < dateB) : (dateA > dateB));
        };

        onRowClick = (purchaseOrder) => {
            window.triggerEvent('purchaseOrderSelected', purchaseOrder);
        };

        render() {
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
                        {this.props.purchaseOrders.filter(this.filterPurchaseOrders).sort(this.sortPurchaseOrders).map(purchaseOrder => {
                            const statusClass = purchaseOrder.status.replace(" ", "_").toLowerCase();
                            return (
                                <div className="row hoverable" onClick={this.onRowClick.bind(this, purchaseOrder)}>
                                    <div className="cell"><span className={"status " + statusClass}>{purchaseOrder.status}</span></div>
                                    <div className="cell">{purchaseOrder.created}</div>
                                    <div className="cell">{purchaseOrder.externalId}</div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            );
        }
    }
