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
        adminCompanyUrl,
        ebaySiteOptions,
        categoryTemplateOptions,
        conditionOptions,
        defaultCurrency
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
                ebaySiteOptions={JSON.parse(ebaySiteOptions)}
                categoryTemplateOptions={JSON.parse(categoryTemplateOptions)}
                conditionOptions={JSON.parse(conditionOptions)}
                defaultCurrency={defaultCurrency}
            />,
            mountingNode
        );
    };

    return Product;
});