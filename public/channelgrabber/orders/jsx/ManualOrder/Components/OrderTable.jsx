define([
    'react'
], function(
    React
) {
    "use strict";
    var OrderTable = React.createClass({
        getDefaultProps: function () {
            return {
                orderRows: []
            }
        },
        render: function () {
            return (
                <div className="order-table-wrapper">
                    {this.props.orderRows.map(function (row) {
                        return <p>{row.variation.sku + ": " + row.quantity}</p>
                    })}
                </div>
            );
        }
    });

    return OrderTable;
});