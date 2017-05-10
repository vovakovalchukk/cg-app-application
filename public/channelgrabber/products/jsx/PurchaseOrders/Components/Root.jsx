define([
    'react',
    'Common/Components/Select',
    'PurchaseOrders/Components/List',
    'PurchaseOrders/Containers/Editor'
], function(
    React,
    Select,
    PurchaseOrdersList,
    PurchaseOrdersEditor
) {
    "use strict";

    const FILTER_OPTIONS = [
        {
            name: 'All',
            value: 'All'
        }, {
            name: 'Complete',
            value: 'Complete',
        }, {
            name: 'In Progress',
            value: 'In Progress'
        }
    ];

    var RootComponent = React.createClass({
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
            <div className="purchase-orders-root">
                <div className="purchase-orders-actions">
                    <div className="purchase-orders-action">
                        <Select prefix="Show" options={FILTER_OPTIONS} onOptionChange={this.props.onFilterSelected}/>
                    </div>
                </div>
                <div className="purchase-orders-container">
                    <PurchaseOrdersList
                        filterStatus={this.props.filterStatus}
                        purchaseOrders={this.props.purchaseOrders}
                    />
                    <PurchaseOrdersEditor />
                </div>
            </div>
            );
        }
    });

    return RootComponent;
});