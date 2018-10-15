import React from 'react';
import ReactDOM from 'react-dom';
import RootContainer from 'PurchaseOrders/Containers/Root';
    var PurchaseOrders = function(mountingNode, utilities)
    {

        ReactDOM.render(
            <RootContainer
                productsUrl="/purchaseOrders/list"
                utilities={utilities}
            />,
            mountingNode
        );
    };

    export default PurchaseOrders;
