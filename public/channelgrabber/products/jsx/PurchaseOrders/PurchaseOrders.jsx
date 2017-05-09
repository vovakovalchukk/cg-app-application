define([
    'react',
    'react-dom',
    'PurchaseOrders/Containers/Root'
], function(
    React,
    ReactDOM,
    RootContainer
) {
    var PurchaseOrders = function(mountingNode, imageBasePath)
    {

        ReactDOM.render(
            <RootContainer
                productsUrl="/purchaseOrders/list"
                imageBasePath={imageBasePath}
            />,
            mountingNode
        );
    };

    return PurchaseOrders;
});