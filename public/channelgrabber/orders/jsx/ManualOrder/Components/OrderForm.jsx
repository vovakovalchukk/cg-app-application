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
        onProductSelected: function (variation, quantity) {
            console.log(variation);
            console.log(quantity);
        },
        render: function () {
            return (
                <div className="order-form-wrapper">
                    <h2>Search for Products to Add</h2>
                    <ProductDropdown onOptionSelected={this.onProductSelected} />
                </div>
            );
        }
    });

    return OrderForm;
});