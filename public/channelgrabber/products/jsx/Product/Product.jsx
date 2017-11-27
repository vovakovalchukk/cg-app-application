define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, utils, searchAvailable, isAdmin, getParamSearchTerm, linkedProductsEnabled)
    {
        ReactDOM.render(
            <RootComponent
                productsUrl="/products/ajax"
                utilities={utils}
                searchAvailable={searchAvailable}
                initialSearchTerm={getParamSearchTerm}
                isAdmin={isAdmin}
                linkedProductsEnabled={linkedProductsEnabled}
            />,
            mountingNode
        );
    };

    return Product;
});