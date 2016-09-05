define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, imageBasePath)
    {
        console.log('Manual Orders');
        ReactDOM.render(<RootComponent productsUrl="/products/ajax" imageBasePath={imageBasePath}/>, mountingNode);
    };

    return Product;
});