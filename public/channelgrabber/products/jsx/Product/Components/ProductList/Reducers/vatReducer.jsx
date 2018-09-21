define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        vatRates: [],
        productsVat: []
    };
    
    var vatReducer = reducerCreator(initialState, {
        "VAT_FROM_PRODUCTS_EXTRACT": function(state, action) {
            console.log('in VAT_FROM_PRODUCTS_EXTRACT -R ');
            let {products} = action.payload;
            
            let newVatRates = getTaxOptionsFromProduct(products[0]);
            let newProductsVat = getChosenVatFromProducts(products);
            
            
            
            //todo -- don't save vatRates again if they exist already
            let vatRates = applyNewTaxRates(state, newVatRates);
            
            // todo - bugfix
            let productsVat = state.productsVat.concat(newProductsVat);
            
            console.log('vatRates saved: ', vatRates);
            console.log('productsVat saved: ', productsVat);
            //todo sort for readability
    
    
            let newState = Object.assign({}, state, {
                vatRates,
                productsVat
            });
            return newState;
        },
    });
    
    return vatReducer;
    
    function getChosenVatFromProducts(products) {
        let productsVat = [];
        products.forEach(product => {
            product = addDummyTaxCountrys(product);
            Object.keys(product.taxRates).forEach((countryCode) => {
                let taxOptionsForCountry = product.taxRates[countryCode];
                Object.keys(taxOptionsForCountry).forEach(taxOptionKey => {
                    let option = taxOptionsForCountry[taxOptionKey];
                    
                    
                    //todo - remove this dummy code after testing 216
                    if(option.name==="french-standard"){
                        let chosenVat = {
                            productId: product.id,
                            countryCode,
                            key: taxOptionKey,
                        };
                        productsVat.push(chosenVat);
                        return;
                    }
                    
                    
                    if (!option.selected) {
                        return;
                    }
                    let chosenVat = {
                        productId: product.id,
                        countryCode,
                        key: taxOptionKey,
                    };
                    productsVat.push(chosenVat);
                    return;
                })
            });
        });
        return productsVat;
    }
    
    function getTaxOptionsFromProduct(product) {
        let options = [];
        // todo - remove this before submission of TAC-216 - for dummy purposes only
        product = addDummyTaxCountrys(product);
    
        Object.keys(product.taxRates).forEach((countryCode) => {
            let taxOptions = product.taxRates[countryCode];
            Object.keys(taxOptions).forEach((taxOptionKey,index) => {
                let option = taxOptions[taxOptionKey];
                let optionToSave = {
                    key: taxOptionKey,
                    countryCode,
                    name: option.name,
                    rate: option.rate,
                };
                options.push(optionToSave);
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
    
    function applyNewTaxRates(state, newVatRates) {
        let vatRatesToAdd = [];
        newVatRates.forEach(newVatRate=>{
            if(!!state.vatRates.find(stateVatRate=>{
                return newVatRate.key === stateVatRate.key;
            })){
                return;
            }
            vatRatesToAdd.push(newVatRate);
        });
        return state.vatRates.concat(vatRatesToAdd);
    }
});