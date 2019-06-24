import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'InvoiceOverview/RootComponent';
    var InvoiceOverview = function(mountingNode, invoiceData)
    {
        ////
        console.log('in InvoiceOverview');
        ////

        //todo - hit endpoints here as a means of testing these.





        ReactDOM.render(React.createElement(RootComponent, invoiceData), mountingNode);
    };

    export default InvoiceOverview;
