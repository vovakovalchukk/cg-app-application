define([
    'react',
    'react-dom',
    'InvoiceOverview/RootComponent'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var InvoiceOverview = function(mountingNode)
    {
        ReactDOM.render(React.createElement(RootComponent), mountingNode);
    };

    return InvoiceOverview;
});