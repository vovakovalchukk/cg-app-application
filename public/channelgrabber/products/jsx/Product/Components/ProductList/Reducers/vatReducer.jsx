define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        vatRates: [],
        productsVat: {}
    };
    
    var vatReducer = reducerCreator(initialState, {
        "VAT_FROM_PRODUCTS_EXTRACT": function(state, action) {
            let {products} = action.payload;
            let vatRates = getTaxOptionsFromProduct(products[0]);
            let newProductsVat = getChosenVatFromProducts(products);
            let productsVat = Object.assign(state.productsVat, newProductsVat);
            productsVat = sortByKey(productsVat);
            
            let newState = Object.assign({}, state, {
                vatRates,
                productsVat
            });
            return newState;
        },
        "VAT_UPDATE_SUCCESS": function(state, action) {
            let {rowId, countryCode, desiredVal, response} = action.payload;
            let newProductsVat = Object.assign({}, state.productsVat);
            n.success('Product tax rate updated successfully.');
            newProductsVat[rowId][countryCode] = desiredVal;
            let newState = Object.assign({}, state, {
                productsVat: newProductsVat
            });
            return newState;
        },
        "VAT_UPDATE_ERROR": function(state, action) {
            let error = action.payload;
            n.showErrorNotification(error, "There was an error when attempting to update the product tax rate.");
            return state;
        }
    });
    
    return vatReducer;
    
    function getChosenVatFromProducts(products) {
        let productsVat = {};
        products.forEach(product => {
            if(!product.taxRates){
                console.error('no tax rates set for product:',product);
                return;
            }
            let chosenVats = {
                productId: product.id
            };
            Object.keys(product.taxRates).forEach((countryCode) => {
                let taxOptionsForCountry = product.taxRates[countryCode];
                Object.keys(taxOptionsForCountry).forEach(taxOptionKey => {
                    let option = taxOptionsForCountry[taxOptionKey];
                    
                    if (!option.selected) {
                        return;
                    }
                    chosenVats[countryCode] = taxOptionKey;
                });
            });
            productsVat[product.id] = chosenVats;
            return;
        });
        return productsVat;
    }
    
    function sortByKey(unordered) {
        const ordered = {};
        Object.keys(unordered).sort().forEach(function(key) {
            ordered[key] = unordered[key];
        });
        return ordered;
    }
    
    function getTaxOptionsFromProduct(product) {
        let options = {};
        
        Object.keys(product.taxRates).forEach((countryCode) => {
            options[countryCode] = [];
            let taxOptions = product.taxRates[countryCode];
            Object.keys(taxOptions).forEach((taxOptionKey, index) => {
                let option = taxOptions[taxOptionKey];
                let optionToSave = {
                    key: taxOptionKey,
                    countryCode,
                    name: option.name,
                    rate: option.rate,
                    label: generateLabel(option)
                };
                options[countryCode].push(optionToSave);
            })
        });
        return options;
    }
    
    function generateLabel(option) {
        return option.rate + "%" + ' (' + option.name + ')';
    }
});