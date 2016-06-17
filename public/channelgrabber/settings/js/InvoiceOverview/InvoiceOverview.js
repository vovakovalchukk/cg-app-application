define([
    'react',
    'react-dom',
    'InvoiceOverview/MainComponent'
], function(
    React,
    ReactDOM,
    MainComponent
) {
    var InvoiceOverview = function(mountingNode, invoiceTemplates)
    {
        ReactDOM.render(React.createElement(MainComponent, invoiceTemplates), mountingNode);
    };

    return InvoiceOverview;
});