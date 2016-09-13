define([
    'react',
    'ManualOrder/Components/OrderRow'
], function(
    React,
    OrderRow
) {
    "use strict";
    var OrderTable = React.createClass({
        getDefaultProps: function () {
            return {
                orderRows: []
            }
        },
        getOrderRows: function () {
            return (
                this.props.orderRows.map(function (row) {
                    return (
                        <OrderRow row={row} onSkuChange={this.onSkuChanged} onStockQuantityUpdate={this.onStockQuantityUpdated}/>
                    )
                }.bind(this))
            );
        },
        onSkuChanged: function () {
            console.log('SKU change');
        },
        onPriceChange: function () {
            console.log('Price change');
        },
        onStockQuantityUpdated: function (sku, quantity) {
            console.log(sku);
            console.log(quantity);
        },
        render: function () {
            return (
                <div className="order-table-wrapper">
                    <div className="order-rows-wrapper">
                        {this.getOrderRows()}
                    </div>
                    <div className="order-footer-wrapper">
                    </div>
                </div>
            );
        }
    });

    return OrderTable;
});