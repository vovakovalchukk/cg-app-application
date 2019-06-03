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
    listingTemplates,
    incPOStockInAvailableOptions,
    ebaySiteOptions,
    categoryTemplateOptions,
    conditionOptions,
    defaultCurrency,
    salesPhoneNumber,
    demoLink,
    showVAT,
    massUnit,
    lengthUnit,
    pickLocations,
    pickLocationValues
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
            listingTemplates={listingTemplates}
            incPOStockInAvailableOptions={incPOStockInAvailableOptions}
            ebaySiteOptions={ebaySiteOptions}
            categoryTemplateOptions={categoryTemplateOptions}
            conditionOptions={conditionOptions}
            defaultCurrency={defaultCurrency}
            salesPhoneNumber={salesPhoneNumber}
            demoLink={demoLink}
            showVAT={showVAT}
            massUnit={massUnit}
            lengthUnit={lengthUnit}
            pickLocations={pickLocations}
            pickLocationValues={pickLocationValues}
        />,
        mountingNode
    );
};

export default Product;
