import React from 'react';
import ReactDOM from 'react-dom';
import InvoiceOverviewRoot from 'InvoiceOverview/Root';

var InvoiceOverview = function(mountingNode, existingTemplates) {
    ReactDOM.render(React.createElement(InvoiceOverviewRoot, existingTemplates), mountingNode);
};

export default InvoiceOverview;