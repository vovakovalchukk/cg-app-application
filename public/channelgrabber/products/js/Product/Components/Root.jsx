define([
    'react',
    'Product/Components/List'
], function(
    React,
    ProductList
) {
    "use strict";

    var RootComponent = React.createClass({
        render: function render() {
            return <ProductList />;
        }
    });

    return RootComponent;
});