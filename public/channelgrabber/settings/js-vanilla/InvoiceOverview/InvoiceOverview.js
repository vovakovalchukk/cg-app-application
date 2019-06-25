import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'InvoiceOverview/RootComponent';

var InvoiceOverview = function(mountingNode, invoiceData)
{
    ////
    console.log('in InvoiceOverview');
    ////

    //todo - hit endpoints here as a means of testing these.


    $.ajax({
        'url' : '/settings/invoice/deleteTemplate',
        'data' : {'id' : '43'},
        'method' : 'POST',
        'dataType' : 'json',
        'error' : function () {
            n.error('Unable to delete shipping aliases');
        }
    });


    ReactDOM.render(React.createElement(RootComponent, invoiceData), mountingNode);
};

export default InvoiceOverview;