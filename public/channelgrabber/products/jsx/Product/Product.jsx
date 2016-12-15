define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(mountingNode, imageBasePath, searchAvailable, isAdmin, getParamSearchTerm)
    {
        ReactDOM.render(
            <RootComponent
                productsUrl="/products/ajax"
                imageBasePath={imageBasePath}
                searchAvailable={searchAvailable}
                initialSearchTerm={getParamSearchTerm}
                isAdmin={isAdmin}
            />,
            mountingNode
        );
    };

    return Product;
});