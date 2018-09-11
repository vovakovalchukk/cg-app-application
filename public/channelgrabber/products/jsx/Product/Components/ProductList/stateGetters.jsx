define([], function() {
    "use strict";
    
    let stateGetters = function(getState) {
        return (function() {
            let _getState = getState;
            let self = {
                getVisibleProducts: () => {
                    return _getState().products.visibleRows;
                },
                getProductById: (id) => {
                    // console.log('in getProductById with id : ' , id);
                    
                    
                    return self.getVisibleProducts().find(product => {
                        // console.log('product in loop: ' , product);
                        
                        
                        return product.id === id;
                    });
                }
            }
            return self;
        })()
    };
    
    return stateGetters;
});