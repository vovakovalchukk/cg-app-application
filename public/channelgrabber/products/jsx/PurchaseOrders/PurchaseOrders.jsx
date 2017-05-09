define([
    'react',
    'react-dom',
    'PurchaseOrders/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, imageBasePath)
    {

        ReactDOM.render(
            <RootComponent
                productsUrl="/purchaseOrders/list"
                imageBasePath={imageBasePath}
            />,
            mountingNode
        );
    };

    return Product;
});