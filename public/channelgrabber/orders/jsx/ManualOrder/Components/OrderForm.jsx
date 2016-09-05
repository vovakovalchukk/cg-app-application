define([
    'react',
    'ManualOrder/Components/ProductDropdown/Dropdown'
], function(
    React,
    ProductDropdown
) {
    "use strict";
    var OrderForm = React.createClass({
        getInitialState: function () {
            return {
                orderRows: []
            }
        },
        onProductSelected: function () {

        },
        render: function () {
            return (
                <div className="order-form-wrapper">
                    <h2>Search for Products to Add</h2>
                    <ProductDropdown dataUrl="/products/ajax" onOptionSelected={this.onProductSelected} />
                </div>
            );
        }
    });

    return OrderForm;
});