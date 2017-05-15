define([
    'react',
    'Common/Components/Select',
    'PurchaseOrders/Components/List',
    'PurchaseOrders/Containers/Editor',
    'Common/Components/Button',
    'Common/Components/Popup'
], function(
    React,
    Select,
    PurchaseOrdersList,
    PurchaseOrdersEditor,
    Button,
    PopupComponent
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
        render: function()
        {
            return (
            <div className="purchase-orders-root">
                <PopupComponent onYesButtonPressed={this.props.onCreateNewPurchaseOrder}>
                    <p>Do you want to create a new Purchase Order?</p>
                    <p>Any unsaved changes to the current Purchase Order will be lost.</p>
                </PopupComponent>
                <div className="purchase-orders-actions">
                    <div className="purchase-orders-action">
                        <Select prefix="Show" options={FILTER_OPTIONS} onOptionChange={this.props.onFilterSelected}/>
                    </div>
                    <div className="purchase-orders-action">
                        <Button onClick={this.props.onCreateNewPurchaseOrderButtonPressed} sprite="sprite-plus-18-black" text="Create Purchase Order"/>
                    </div>
                </div>
                <div className="purchase-orders-container">
                    <PurchaseOrdersList
                        filterStatus={this.props.filterStatus}
                        sortAsc={this.props.sortAsc}
                        purchaseOrders={this.props.purchaseOrders}
                        onPurchaseOrderSelected={this.props.onPurchaseOrderSelected}
                        onDateColumnClicked={this.props.onDateColumnClicked}
                    />
                    <PurchaseOrdersEditor />
                </div>
            </div>
            );
        }
    });

    return RootComponent;
});