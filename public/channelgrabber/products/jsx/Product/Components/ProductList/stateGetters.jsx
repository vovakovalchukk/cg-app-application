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
                },
                getPaginationLimit() {
                    return _getState().pagination.limit;
                },
                getCurrentPageNumber() {
                    return _getState().pagination.page;
                },
                getCurrentSearchTerm() {
                    return _getState().search.searchTerm;
                },
                getVisibleFixedColumns() {
                    return _getState().columns.columnSettings.filter((column) => {
                        return column.fixed
                    });
                },
                getStock(id){
                    return self.getProductById(id).stock;
                }
            };
            return self;
        })()
    };
    
    return stateGetters;
});