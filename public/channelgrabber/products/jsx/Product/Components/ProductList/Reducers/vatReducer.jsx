import reducerCreator from 'Common/Reducers/creator';

"use strict";

let initialState = {
    countries: {},
    vatRates: [],
    productsVat: {}
};

let vatReducer = reducerCreator(initialState, {
    "VAT_FROM_PRODUCTS_EXTRACT": function(state, action) {
        let {products} = action.payload;
        let countries = getCountries(products);
        let newProductsVat = getChosenVatFromProducts(products, countries);

        let productsVat = Object.assign(state.productsVat, newProductsVat);
        productsVat = sortByKey(productsVat);

        let newState = Object.assign({}, state, {
            productsVat,
            countries
        });
        return newState;
    },
    "VAT_RATES_STORE": function(state, action) {
        let {vatRates} = action.payload;

        vatRates = formatTaxOptions(vatRates);
        let newState = Object.assign({}, state, {
            vatRates
        });
        return newState;
    },
    "VAT_UPDATE_SUCCESS": function(state, action) {
        let {rowId, countryCode, desiredVal, response} = action.payload;
        let newProductsVat = Object.assign({}, state.productsVat);
        n.success('Product tax rate updated successfully.');
        newProductsVat[countryCode].byProductId[rowId].key = desiredVal;
        let newState = Object.assign({}, state, {
            productsVat: newProductsVat
        });
        return newState;
    },
    "VAT_UPDATE_ERROR": function(state, action) {
        let error = action.payload;
        n.showErrorNotification(error, "There was an error when attempting to update the product tax rate.");
        return state;
    },
    "VAT_SELECT_TOGGLE": function(state, action) {
        let {productId, countryCode} = action.payload;
        let vat = Object.assign({}, state);
        let productVat = vat.productsVat[countryCode].byProductId[productId];
        let previousActiveProp = productVat.active;
        vat = makeAllVatSelectsInactive(vat, productId);
        if(previousActiveProp){
            delete productVat.active;
            return vat;
        }
        productVat.active = true;
        return vat;
    }
});

export default vatReducer;

function applyCountryCodesToState(productsVat, countries) {
    for (let countryCode of countries.allIds) {
        productsVat[countryCode] = {
            byProductId: {}
        }
    }
    return productsVat;
}

function getChosenVatFromProducts(products, countries) {
    let productsVat = {
        allProductIds: []
    };

    productsVat = applyCountryCodesToState(productsVat, countries);
    for (let product of products) {
        if (!product.taxRates) {
            continue;
        }
        productsVat.allProductIds.push(product.id);
        for (let countryCode of Object.keys(product.taxRates)) {
            let taxOptionsForCountry = product.taxRates[countryCode];
            for (let taxOptionKey of Object.keys(taxOptionsForCountry)) {
                let option = taxOptionsForCountry[taxOptionKey];
                if (!option.selected) {
                    continue;
                }
                productsVat[countryCode].byProductId[product.id] = {
                    productId: product.id,
                    key: taxOptionKey
                };
            }
        }
    }
    return productsVat;
}

function sortByKey(unordered) {
    const ordered = {};
    Object.keys(unordered).sort().forEach(function(key) {
        ordered[key] = unordered[key];
    });
    return ordered;
}

function getCountries(products) {
    let countries = {
        byId: {},
        allIds: []
    };
    for (let product of products) {
        if (!product.taxRates) {
            continue;
        }
        for (let countryCode of Object.keys(product.taxRates)) {
            countries.byId[countryCode] = {
                countryCode
            };
            countries.allIds.push(countryCode);
        }
        break;
    }
    return countries;
}

function formatTaxOptions(taxRates) {
    let options = {};
    Object.keys(taxRates).forEach((countryCode) => {
        options[countryCode] = {};
        Object.keys(taxRates[countryCode]).forEach(taxRateId => {
            let option = taxRates[countryCode][taxRateId];
            options[countryCode][taxRateId] = {
                key: taxRateId,
                countryCode,
                name: option.name,
                rate: option.rate,
                label: generateLabel(option)
            }
        })
    })
    return options;
}

function generateLabel(option) {
    return option.rate + "%" + ' (' + option.name + ')';
}

function makeAllVatSelectsInactive(vat, toggledProductId) {
    let vatCopy = Object.assign({}, vat);
    let countryCodes = vatCopy.countries.allIds;

    for (let country of countryCodes) {
        for (let productId of vatCopy.productsVat.allProductIds) {
            delete vatCopy.productsVat[country].byProductId[productId].active;
        }
    }
    return vatCopy;
}