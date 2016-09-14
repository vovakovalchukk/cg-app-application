define([
    'react',
    'ManualOrder/Components/ProductDropdown/Dropdown',
    'ManualOrder/Components/OrderTable'
], function(
    React,
    ProductDropdown,
    OrderTable
) {
    "use strict";
    var OrderForm = React.createClass({

        render: function () {
            return (
                <div className="order-form-wrapper">
                    <h2>Search for Products to Add</h2>
                    <ProductDropdown />
                    <OrderTable />
                </div>
            );
        }
    });

    return OrderForm;
});