define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode)
    {
        ReactDOM.render(<RootComponent />, mountingNode);
    };

    return Product;
});