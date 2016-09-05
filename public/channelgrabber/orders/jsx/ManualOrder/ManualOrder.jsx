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
        ReactDOM.render(<RootComponent imageBasePath={imageBasePath}/>, mountingNode);
    };

    return Product;
});