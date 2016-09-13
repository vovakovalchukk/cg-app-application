define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, manualOrderUtils)
    {
        ReactDOM.render(<RootComponent manualOrderUtils={manualOrderUtils}/>, mountingNode);
    };

    return Product;
});