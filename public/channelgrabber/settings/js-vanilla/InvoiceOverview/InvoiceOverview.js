import React from 'react';
import ReactDOM from 'react-dom';
import InvoiceOverviewRoot from 'InvoiceOverview/Root';

var InvoiceOverview = function(mountingNode, invoiceData) {
    ReactDOM.render(React.createElement(InvoiceOverviewRoot, invoiceData), mountingNode);
};

export default InvoiceOverview;