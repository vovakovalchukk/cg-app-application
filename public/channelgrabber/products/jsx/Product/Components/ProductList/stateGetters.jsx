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
                    return self.getVisibleProducts().find(product => {
                        return product.id === id;
                    });
                }
            }
            return self;
        })()
    };
    
    return stateGetters;
});