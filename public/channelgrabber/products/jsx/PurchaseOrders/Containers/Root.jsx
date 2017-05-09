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
                purchaseOrders: [{status: 'In Progress'}, {status: 'Complete'}, {status: 'Complete'}]
            }
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