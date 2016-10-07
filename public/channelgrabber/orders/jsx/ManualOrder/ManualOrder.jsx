define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, utilities)
    {
        ReactDOM.render(<RootComponent utilities={utilities}/>, mountingNode);
    };

    return Product;
});