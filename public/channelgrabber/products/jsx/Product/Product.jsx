import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'Product/Components/Root';

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
    salesPhoneNumber,
    demoLink,
    showVAT,
    massUnit,
    lengthUnit
) {
    ReactDOM.render(
        <RootComponent
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
            demoLink={demoLink}
            showVAT={showVAT}
            massUnit={massUnit}
            lengthUnit={lengthUnit}
        />,
        mountingNode
    );
};

export default Product;
