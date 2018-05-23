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
                ebaySiteOptions={JSON.parse(ebaySiteOptions)}
                categoryTemplateOptions={JSON.parse(categoryTemplateOptions)}
                conditionOptions={JSON.parse(conditionOptions)}
                defaultCurrency={defaultCurrency}
                salesPhoneNumber={salesPhoneNumber}
            />,
            mountingNode
        );
    };

    return Product;
});