define([
    'react',
    'react-dom',
    'InvoiceOverview/RootComponent'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var InvoiceOverview = function(mountingNode, invoiceData)
    {
        ReactDOM.render(React.createElement(RootComponent, JSON.parse(invoiceData)), mountingNode);
    };

    return InvoiceOverview;
});