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
                filterStatus: 'All',
                purchaseOrders: []
            }
        },
        getChildContext: function() {
            return {
                imageUtils: this.props.utilities.image
            };
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
                <RootComponent
                    filterStatus={this.state.filterStatus}
                    purchaseOrders={this.state.purchaseOrders}
                    onFilterSelected={function(selection){this.setState({filterStatus: selection.value})}.bind(this)}
                />
            );
        }
    });

    RootContainer.childContextTypes = {
        imageUtils: React.PropTypes.object
    };

    return RootContainer;
});