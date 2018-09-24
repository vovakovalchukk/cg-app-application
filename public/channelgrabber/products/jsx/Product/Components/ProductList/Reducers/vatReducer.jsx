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
        }
    });
    
    return vatReducer;
    
    function getChosenVatFromProducts(products) {
        let productsVat = {};
        products.forEach(product => {
            product = addDummyTaxCountrys(product);
            let chosenVats = {
                productId: product.id
            };
            Object.keys(product.taxRates).forEach((countryCode) => {
                let taxOptionsForCountry = product.taxRates[countryCode];
                Object.keys(taxOptionsForCountry).forEach(taxOptionKey => {
                    let option = taxOptionsForCountry[taxOptionKey];
                    
                    //todo - remove this dummy code after testing 216
                    if (option.name === "french-standard") {
                        chosenVats[countryCode] = taxOptionKey;
                        return;
                    }
                    // ^^^
                    
                    
                    if (!option.selected) {
                        return;
                    }
                    chosenVats[countryCode] = taxOptionKey;
                });
            });
            productsVat[product.id]=chosenVats;
            return;
        });
        return productsVat;
    }
    
    function sortByKey(unordered){
        const ordered = {};
        Object.keys(unordered).sort().forEach(function(key) {
            ordered[key] = unordered[key];
        });
        return ordered;
    }
    
    function getTaxOptionsFromProduct(product) {
        let options = {};
        
        // todo - remove this before submission of TAC-216 - for dummy purposes only
        product = addDummyTaxCountrys(product);
        //^^^^
        
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
                };
                options[countryCode].push(optionToSave);
            })
        });
        return options;
    }
    
    //TODO -remove after getting to end of 216
    function addDummyTaxCountrys(product) {
        product.taxRates["FRA"] = {
            FR1: {
                name: "french-standard",
                rate: "20"
            }, FR2: {
                name: "great",
                rate: "25"
            }, FR3: {
                name: "excellent",
                rate: "10"
            }
        };
        return product;
    }
});