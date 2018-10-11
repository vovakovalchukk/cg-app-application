import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'InvoiceOverview/RootComponent';
    var InvoiceOverview = function(mountingNode, invoiceData)
    {
        ReactDOM.render(React.createElement(RootComponent, JSON.parse(invoiceData)), mountingNode);
    };

    export default InvoiceOverview;
