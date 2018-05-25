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
        taxRates,
        stockModeOptions,
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
                taxRates={taxRates}
                stockModeOptions={stockModeOptions}
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