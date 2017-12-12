define([
    'react',
    'react-dom',
    'PurchaseOrders/Containers/Root'
], function(
    React,
    ReactDOM,
    RootContainer
) {
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

    return PurchaseOrders;
});