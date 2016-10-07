define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var ManualOrder = function(mountingNode, utilities)
    {
        ReactDOM.render(<RootComponent utilities={utilities}/>, mountingNode);
    };

    return ManualOrder;
});