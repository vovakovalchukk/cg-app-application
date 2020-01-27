import React from 'react';
import ReactDOM from 'react-dom';
import RootContainer from 'PurchaseOrders/Containers/Root';
    var PurchaseOrders = function(
        mountingNode,
        utilities,
        supplierOptions
    ) {

        ReactDOM.render(
            <RootContainer
                productsUrl="/purchaseOrders/list"
                utilities={utilities}
                supplierOptions={supplierOptions}
            />,
            mountingNode
        );
    };

    export default PurchaseOrders;
