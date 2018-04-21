define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function (
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function (
        mountingNode,
        utils,
        searchAvailable,
        isAdmin,
        getParamSearchTerm,
        features,
        adminCompanyUrl
    ) {
        ReactDOM.render(
            <RootComponent
                productsUrl="/products/ajax"
                utilities={utils}
                searchAvailable={searchAvailable}
                initialSearchTerm={getParamSearchTerm}
                isAdmin={isAdmin}
                features={features}
                adminCompanyUrl={adminCompanyUrl}
            />,
            mountingNode
        );
    };
    
    return Product;
});