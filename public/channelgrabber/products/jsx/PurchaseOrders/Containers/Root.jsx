import React from 'react';
import RootComponent from 'PurchaseOrders/Components/Root';
    

    var RootContainer = React.createClass({
        getInitialState: function () {
            return {
                filterStatus: 'All',
                sortAsc: true,
                purchaseOrders: []
            }
        },
        getChildContext: function() {
            return {
                imageUtils: this.props.utilities.image
            };
        },
        componentDidMount: function () {
            this.purchaseOrderRequest = this.doPurchaseOrderRequest();
            window.addEventListener('purchaseOrderListRefresh', this.doPurchaseOrderRequest);
        },
        componentWillUnmount: function () {
            this.purchaseOrderRequest.abort();
            window.removeEventListener('purchaseOrderListRefresh', this.doPurchaseOrderRequest);
        },
        doPurchaseOrderRequest: function () {
            $.ajax({
                method: 'POST',
                url: '/products/purchaseOrders/list',
                success: function (response) {
                    if (response.list === undefined || response.list.length === 0) {
                        return;
                    }
                    this.setState({
                        purchaseOrders: response.list
                    });
                }.bind(this)
            });
        },
        onCreateNewPurchaseOrderButtonPressed: function () {
            window.triggerEvent('triggerPopup');
        },
        onCreateNewPurchaseOrder: function () {
            window.triggerEvent('createNewPurchaseOrder');
        },
        onDateColumnClicked: function () {
            this.setState({
                sortAsc: !this.state.sortAsc
            });
        },
        render: function()
        {
            return (
                <RootComponent
                    filterStatus={this.state.filterStatus}
                    sortAsc={this.state.sortAsc}
                    purchaseOrders={this.state.purchaseOrders}
                    onFilterSelected={function(selection){this.setState({filterStatus: selection.value})}.bind(this)}
                    onCreateNewPurchaseOrder={this.onCreateNewPurchaseOrder}
                    onCreateNewPurchaseOrderButtonPressed={this.onCreateNewPurchaseOrderButtonPressed}
                    onDateColumnClicked={this.onDateColumnClicked}
                />
            );
        }
    });

    RootContainer.childContextTypes = {
        imageUtils: React.PropTypes.object
    };

    export default RootContainer;
