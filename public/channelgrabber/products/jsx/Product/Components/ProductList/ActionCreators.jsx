define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity'
], function(
    AjaxHandler,
    ProductFilter
) {
    "use strict";
    // fetchVariations: function(filter) {
    //     $('#products-loading-message').show();
    //
    //     function onSuccess(data) {
    //         var variationsByParent = this.sortVariationsByParentId(data.products, filter.getParentProductId());
    //         this.setState({
    //             variations: variationsByParent
    //         }, function() {
    //             this.fetchLinkedProducts();
    //             $('#products-loading-message').hide()
    //         }.bind(this));
    //     }
    //
    //     AjaxHandler.fetchByFilter(filter, onSuccess.bind(this));
    // },
    return {
        initialSimpleAndParentProductsLoad: (products) => {
            return {
                type: "INITIAL_SIMPLE_AND_PARENT_PRODUCTS_LOAD",
                payload: {
                    products
                }
            };
        },
        expandProduct: (productRowIdToExpand) => {
            return function(dispatch, getState) {
                
                //todo need to do something where it pulls on existing data if they exist
                //IF IT DOESN EXIST ALREADY
                
                
                console.log('aq - in epxandProduct with productRowIdToExpand: ' , productRowIdToExpand);
                dispatch({
                    type: 'PRODUCT_VARIATIONS_GET_REQUEST'
                });
    
                var filter = new ProductFilter(null, productRowIdToExpand);
                
                let fetchProductVariationsCallback = function(data){
                    // console.log('data.products: ', data.products);
                    var variationsByParent = sortVariationsByParentId(data.products, filter.getParentProductId());
                    dispatch({
                        type: 'PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS',
                        payload:variationsByParent
                    });
                    // console.log('about to dispatch the product expand...');
                    dispatch({
                        type: 'PRODUCT_EXPAND',
                        payload:{
                            productRowIdToExpand
                        }
                    });
                };
                AjaxHandler.fetchByFilter(filter,fetchProductVariationsCallback);
            }
        },
        collapseProduct:(productRowIdToCollapse )=>{
            return{
                type:"PRODUCT_COLLAPSE",
                payload:{
                    productRowIdToCollapse
                }
            }
        }
    };
    function sortVariationsByParentId(newVariations, parentProductId) {
        var variationsByParent = {};
        // if (parentProductId) {
        //     variationsByParent = this.state.variations;
        //     variationsByParent[parentProductId] = newVariations;
        //     return variationsByParent;
        // }
        for (var index in newVariations) {
            var variation = newVariations[index];
            if (!variationsByParent[variation.parentProductId]) {
                variationsByParent[variation.parentProductId] = [];
            }
            variationsByParent[variation.parentProductId].push(variation);
        }
        return variationsByParent;
    }
});
