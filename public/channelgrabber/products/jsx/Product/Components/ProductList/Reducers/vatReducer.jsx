import reducerCreator from 'Common/Reducers/creator';

"use strict";

/*
the state shape with example entries
vat : {
    varRates: [],
    GB: {
        byProductId:{
            1: {
                productId: 1
                key: "GB1",
                active: false
            }
        }
    },
    FR ...
}
*/

let initialState = {
    vatRates: [],
    productsVat: {}
};

let vatReducer = reducerCreator(initialState, {
    "VAT_FROM_PRODUCTS_EXTRACT": function(state, action) {
        let {products} = action.payload;
        let newProductsVat = getChosenVatFromProducts(products);

        let productsVat = Object.assign(state.productsVat, newProductsVat);
        productsVat = sortByKey(productsVat);

        let newState = Object.assign({}, state, {
            productsVat
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
        let {productId, row} = action.payload;
        let stateCopy = Object.assign({}, state);
         return stateCopy;
//        let stockModeExists = !!state.stockModes.byProductId[productId];
//        stockModes = makeAllStockModesInactiveApartFromOneAtSpecifiedProductId(stockModes, productId);
//
//        if (stockModeExists) {
//            stockModes.byProductId[productId].value = stockModes.byProductId[productId] ? stockModes.byProductId[productId].value : currentStock.stockMode;
//            stockModes.byProductId[productId].active = !stockModes.byProductId[productId].active;
//            return applyStockModesToState(stateCopy, stockModes)
//        }
//
//        stockModes.byProductId[productId] = {
//            value: stockModes.byProductId[productId] ? stockModes.byProductId[productId] : currentStock.stockMode,
//            valueEdited: '',
//            active: true
//        };
//        return applyStockModesToState(stateCopy, stockModes)
    },
});

export default vatReducer;

function applyCountryCodesToState(products, productsVat) {
    for (let product of products) {
        if (!product.taxRates) {
            continue;
        }
        for (let countryCode of Object.keys(product.taxRates)) {
            productsVat[countryCode] = {
                byProductId: {}
            }
        }
        break;
    }
    return productsVat;
}

function getChosenVatFromProducts(products) {
    let productsVat = {};

    productsVat = applyCountryCodesToState(products, productsVat);

    for(let product of products){
        if (!product.taxRates) {
            continue;
        }
        for(let countryCode of Object.keys(product.taxRates)){
            let taxOptionsForCountry = product.taxRates[countryCode];
            for(let taxOptionKey of Object.keys(taxOptionsForCountry)){
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
    });

    return options;
}

function generateLabel(option) {
    return option.rate + "%" + ' (' + option.name + ')';
}