define([
    'react',
    'react-dom',
    'Product/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Product = function(
        mountingNode,
        utils,
        searchAvailable,
        listingCreationAllowed,
        managePackageUrl,
        isAdmin,
        getParamSearchTerm,
        features,
        adminCompanyUrl,
        taxRates,
        stockModeOptions,
        ebaySiteOptions,
        categoryTemplateOptions,
        conditionOptions,
        defaultCurrency,
        salesPhoneNumber
    ) {
        ReactDOM.render(
            <RootComponent
                productsUrl="/products/ajax"
                utilities={utils}
                searchAvailable={searchAvailable}
                listingCreationAllowed={listingCreationAllowed}
                initialSearchTerm={getParamSearchTerm}
                isAdmin={isAdmin}
                managePackageUrl={managePackageUrl}
                features={features}
                adminCompanyUrl={adminCompanyUrl}
                taxRates={taxRates}
                stockModeOptions={stockModeOptions}
                ebaySiteOptions={ebaySiteOptions}
                categoryTemplateOptions={categoryTemplateOptions}
                conditionOptions={conditionOptions}
                defaultCurrency={defaultCurrency}
                salesPhoneNumber={salesPhoneNumber}
            />,
            mountingNode
        );
    };

    return Product;
});