define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, imageBasePath)
    {
        ReactDOM.render(<RootComponent productsUrl="/products/ajax" imageBasePath={imageBasePath} />, mountingNode);
    };

    return Product;
});