define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, imageBasePath, searchAvailable)
    {
        console.log('producstsda here compiles');
        ReactDOM.render(<RootComponent productsUrl="/products/ajax" imageBasePath={imageBasePath} searchAvailable={searchAvailable}/>, mountingNode);
    };

    return Product;
});