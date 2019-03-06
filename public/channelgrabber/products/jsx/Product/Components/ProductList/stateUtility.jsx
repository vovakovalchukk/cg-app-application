let stateUtility = function() {
    let self = {
        getProductIndex: (products, productId) => {
            return products.findIndex((product) => {
                return product.id === productId;
            });
        },
        getProductById: (products, productId) => {
            return products.find((product) => {
                return product.id === productId;
            });
        },
        getRowData: (products, rowIndex) => {
            return products.visibleRows[rowIndex];
        },
        getVisibleProducts: (products) => {
            return products.visibleRows;
        },
        getAllParentProductIds: (productsState) => {
            let visibleProducts = self.getVisibleProducts(productsState);
            let parentProductIds = [];
            for (let product of visibleProducts) {
                if (!self.isParentProduct(product)) {
                    continue;
                }
                parentProductIds.push(product.id);
            }
            return parentProductIds;
        },
        getCellData: (products, columnKey, rowIndex) => {
            let row = products.visibleRows[rowIndex];
            let keyToCellDataMap = {
                sku: row['sku'],
                image: getImageData(row),
                available: stateUtility().getStockAvailable(row),
                allocated: stateUtility().getAllocatedStock(row)
            };
            let cellData = keyToCellDataMap[columnKey];
            if (columnKey.indexOf('dummy') > -1) {
                cellData = `${columnKey} ${rowIndex}`;
            }
            return cellData;
        },
        isParentProduct: (rowData) => {
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        isVariation: (rowData) => {
            return !!rowData.parentProductId;
        },
        isSimpleProduct: (rowData) => {
            return !self.isParentProduct(rowData) && !self.isVariation(rowData);
        },
        getStockAvailable(rowData) {
            return stateUtility().getOnHandStock(rowData) - Math.max(stateUtility().getAllocatedStock(rowData), 0);
        },
        getOnHandStock: function(rowData) {
            return (rowData.stock ? rowData.stock.locations[0].onHand : '');
        },
        getAllocatedStock: function(rowData) {
            return (rowData.stock ? rowData.stock.locations[0].allocated : '');
        },
        getProductIdFromSku(products, sku) {
            return products.find((product) => {
                return product.sku === sku;
            }).id
        },
        sortVariationsByParentId(newVariations) {
            let variationsByParent = {};
            for (var index in newVariations) {
                let variation = newVariations[index];
                if (!variationsByParent[variation.parentProductId]) {
                    variationsByParent[variation.parentProductId] = [];
                }
                variationsByParent[variation.parentProductId].push(variation);
            }
            return variationsByParent;
        },
        getDefaultStockModeFromProducts(products) {
            if(!products.length){
                return;
            }
            return products[0].stockModeDefault;
        },
        getDefaultStockLevelFromProducts(products) {
            if(!products.length){
                return;
            }
            return products[0].stockLevelDefault;
        }
    };
    
    return self;
};

export default stateUtility();

function getImageData(row) {
    if (!row.images || !row.images.length) {
        return;
    }
    let primaryImage = row.images[0];
    return {
        id: primaryImage.id,
        url: primaryImage.url
    };
}