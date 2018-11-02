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
                getAccounts() {
                    return _getState().accounts;
                },
                getVat(){
                  return _getState().vat;
                },
                getStock(id){
                    return self.getProductById(id).stock;
                },
//                getStockPrevValuesBeforeEdits(){
//                    return _getState().stock.prevValuesBeforeEdits;
//                },
                getSelectedProducts(){
                    return _getState().bulkSelect.selectedProducts;
                },
            };
            return self;
        })()
    };
  
    export default stateGetters;