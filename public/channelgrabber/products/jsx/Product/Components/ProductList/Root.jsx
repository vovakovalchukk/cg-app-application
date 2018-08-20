define([
    'react',
    'Product/Components/ProductList/ProductList'
], function(
    React,
    ProductList
) {
    "use strict";
    
    var ProductListRoot = React.createClass({
        getDefaultProps: function() {
            return {
                products: [],
                features: {}
            };
        },
        render: function() {
            return (
                <ProductList {...this.props} />
            );
        }
    });
    
    return ProductListRoot;
});
