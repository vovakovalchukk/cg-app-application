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
        getAllVariationsCount: (products) => {
            return products.reduce((totalVariationsCount, product) => {
                return totalVariationsCount + product.variationCount;
            }, 0);
        },
        getCellData: (products, columnKey, rowIndex) => {
            let row = products.visibleRows[rowIndex];
            let keyToCellDataMap = {
                sku: row['sku'],
                image: getImageData(row),
                available: stateUtility().getStockAvailable(row),
                allocated: stateUtility().getAllocatedStock(row),
                onPurchaseOrder: stateUtility().getOnPurchaseOrderStock(row)
            };
            let cellData = keyToCellDataMap[columnKey];
            if (columnKey.indexOf('dummy') > -1) {
                cellData = `${columnKey} ${rowIndex}`;
            }
            return cellData;
        },
        shouldShowSelect({product, select, columnKey, containerElement, scroll, rows, selectIndexOfCell}) {
            let isCurrentActive = self.isCurrentActiveSelect(product, select, columnKey, selectIndexOfCell);
            if (!containerElement) {
                return false;
            }
            let elementRect = containerElement.getBoundingClientRect();

            if (!isCurrentActive || scroll.userScrolling || !rows.initialModifyHasOccurred || hasTheElementBeenObscuredByTableElements(elementRect)) {
                return false;
            }

            return true;
        },
        isCurrentActiveSelect(product, select, columnKey, index) {
            return select.activeSelect.productId === product.id &&
                select.activeSelect.columnKey === columnKey &&
                doesIndexMatch(select, index);
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
            return stateUtility().hasStockData(rowData) ? rowData.stock.locations[0].onHand : '';
        },
        getAllocatedStock: function(rowData) {
            return stateUtility().hasStockData(rowData) ? rowData.stock.locations[0].allocated : '';
        },
        getOnPurchaseOrderStock: function(rowData) {
            return stateUtility().hasStockData(rowData) ? rowData.stock.locations[0].onPurchaseOrder : '';
        },
        hasStockData: function(rowData) {
            return rowData.stock && rowData.stock.locations[0];
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
            if (!products.length) {
                return;
            }
            return products[0].stockModeDefault;
        },
        getDefaultStockLevelFromProducts(products) {
            if (!products.length) {
                return;
            }
            return products[0].stockLevelDefault;
        },
        getLowStockThresholdDefaultsFromProducts(products) {
            if (products.length === 0) {
                return {
                    toggle: false,
                    value: null
                }
            }

            return products[0].lowStockThresholdDefault;
        },
        getLowStockThresholdForProduct(product, stock) {
            return {
                toggle: stock.lowStockThresholdToggle[product.id] ? stock.lowStockThresholdToggle[product.id] : null,
                value: stock.lowStockThresholdValue[product.id] ? stock.lowStockThresholdValue[product.id] : null
            }
        },
        getReorderQuantityForProduct(product, stock) {
            return stock.reorderQuantity[product.id] ? stock.reorderQuantity[product.id] : null;
        },
        getDefaultReorderQuantityFromProducts(products) {
            return products.length === 0 ? null : products[0].reorderQuantityDefault;
        },
        getCellIdentifier(products, rowIndex, columnKey) {
            const row = self.getRowData(products, rowIndex);
            return columnKey + row.id;
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

function doesIndexMatch(select, index) {
    return index === select.activeSelect.index
}

function isTableHeaderObscuringElement(elementRect) {
    return elementRect.top < 130;
}

function isTableFooterObscuringElement(elementRect) {
    return (document.documentElement.scrollHeight - elementRect.bottom) < 0;
}

function hasTheElementBeenObscuredByTableElements(elementRect) {
    return isTableHeaderObscuringElement(elementRect) || isTableFooterObscuringElement(elementRect);
}
