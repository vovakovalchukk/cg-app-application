define([
    'react',
    'Product/Components/ProductRow'
], function(
    React,
    ProductRow
) {
    "use strict";

    var ListComponent = React.createClass({
        render: function()
        {
            return (
                <div id="products-list">
                    {this.props.products.map(function(object) {
                        return <ProductRow key={object.id} data={object}/>;
                    })}
                </div>
            );

        }
    });

    return ListComponent;
});