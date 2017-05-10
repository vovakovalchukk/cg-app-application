define([
    'react',
    'PurchaseOrders/Components/Root'
], function(
    React,
    RootComponent
) {
    "use strict";

    var RootContainer = React.createClass({
        getInitialState: function () {
            return {
                filterStatus: 'Complete',
                purchaseOrders: []
            }
        },
        componentDidMount: function () {
            var self = this;
            this.purchaseOrderRequest = $.ajax({
                method: 'POST',
                url: '/products/purchaseOrders/list',
                success: function (response) {
                    if (response.list === undefined || response.list.length === 0) {
                        return;
                    }
                    self.setState({
                        purchaseOrders: response.list
                    });
                }
            });
        },
        componentWillUnmount: function () {
            this.purchaseOrderRequest.abort();
        },
        render: function()
        {
            return (
                <RootComponent filterStatus={this.state.filterStatus} purchaseOrders={this.state.purchaseOrders}/>
            );
        }
    });

    return RootContainer;
});