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
            // return{
            //     type:"PRODUCT_EXPAND",
            //     payload:{
            //         productRowIdToExpand
            //     }
            // }
            return function(dispatch, getState) {
                console.log('in epxandProduct with productRowIdToExpand: ' , productRowIdToExpand);
                var filter = new ProductFilter(null, event.detail.productId);
    
    
                dispatch({
                    type: 'PRODUCT_EXPAND_REQUEST'
                });
                // let filter =
                
    
                let callback = function(data){
                    console.log('in callback with data ' , data, ' and dispatch : ', dispatch);
    
                    var variationsByParent = sortVariationsByParentId(data.products, filter.getParentProductId());
                    console.log('variationsByParent in callback: ', variationsByParent);
                    
                    dispatch({
                        type: 'PRODUCT_EXPAND_SUCCESS',
                        payload:data
                    })
                };
                AjaxHandler.fetchByFilter(filter,callback.bind(dispatch));
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
