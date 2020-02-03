import React from 'react';
import EditorComponent from 'PurchaseOrders/Components/Editor';
import ProductFilter from 'Product/Filter/Entity';
import AjaxHandler from 'Product/Storage/Ajax';

var COMPLETE_STATUS = "Complete";
var DEFAULT_PO_STATUS = "In Progress";
var DEFAULT_PO_NUMBER = "Enter Purchase Order Number";

const DEFAULT_REORDER_QUANTITY = 1;

class EditorContainer extends React.Component {
    state = {
        purchaseOrderId: 0,
        purchaseOrderStatus: DEFAULT_PO_STATUS,
        purchaseOrderNumber: DEFAULT_PO_NUMBER,
        purchaseOrderItems: []
    };

    componentDidMount() {
        window.addEventListener('createNewPurchaseOrder', this.resetEditor);
        window.addEventListener('purchaseOrderSelected', this.populateEditor);
        window.addEventListener('productSelection', this.onProductSelected);
        window.addEventListener('purchaseOrderListRefresh', this.resetEditor);
        window.addEventListener('createNewPurchaseOrderForSupplier', (event) => {
            this.resetEditor(
                this.fetchProductsBySupplier(event.detail.supplier)
            );
        });

        window.addEventListener('createNewPurchaseOrderForLowStockProducts', () => {
            this.resetEditor(
                this.fetchProductsWithLowStock()
            );
        });
    }

    componentWillUnmount() {
        window.removeEventListener('createNewPurchaseOrder', this.resetEditor);
        window.removeEventListener('purchaseOrderSelected', this.populateEditor);
        window.removeEventListener('productSelection', this.onProductSelected);
        this.completePurchaseOrderRequest.abort();
        this.downloadPurchaseOrderRequest.abort();
        this.deletePurchaseOrderRequest.abort();
        this.savePurchaseOrderRequest.abort();
    }

    componentDidUpdate(prevProps, prevState) {
        let poItemsCount = this.state.purchaseOrderItems.length;
        if (poItemsCount === prevState.purchaseOrderItems.length) {
            return;
        }

        this.props.setEditorEmptyFlag(poItemsCount === 0);
    }

    populateEditor = (event) => {
        if (! event.detail) {
            return;
        }

        this.resetEditor(() => {
            var purchaseOrder = event.detail;
            if (purchaseOrder.items && purchaseOrder.items.length) {
                this.addItemRowMulti(purchaseOrder.items);
            }
            this.setState({
                purchaseOrderId: purchaseOrder.id,
                purchaseOrderStatus: purchaseOrder.status,
                purchaseOrderNumber: purchaseOrder.externalId
            });
        });
    };

    resetEditor = (afterResetCallback) => {
        if (typeof afterResetCallback !== 'function') {
            afterResetCallback = null;
        }
        this.setState({
            purchaseOrderNumber: DEFAULT_PO_NUMBER,
            purchaseOrderStatus: DEFAULT_PO_STATUS,
            purchaseOrderId: 0,
            purchaseOrderItems: []
        }, afterResetCallback);
    };

    onProductSelected = (e) => {
        var data = e.detail;
        this.addItemRow(data.product, data.sku, data.quantity);
    };

    addItemRowMulti = (items) => {
        var purchaseOrderItems = this.state.purchaseOrderItems.slice();

        items.forEach(function (item) {
            var alreadyAddedToForm = purchaseOrderItems.find(function (row) {
                if (row.sku === item.sku) {
                    row.quantity += parseInt(item.quantity);
                    return true;
                }
            });
            if (! alreadyAddedToForm) {
                purchaseOrderItems.push({id: item.id, product: item.product, sku: item.sku, quantity: item.quantity});
            }
        });

        this.setState({
            purchaseOrderItems
        });
    };

    addItemRow = (product, sku, quantity) => {
        let purchaseOrderItems = this.state.purchaseOrderItems.slice();

        var alreadyAddedToForm = purchaseOrderItems.find(function (row) {
            if (row.sku === sku) {
                row.quantity += parseInt(quantity);
                return true;
            }
        });
        if (! alreadyAddedToForm) {
            purchaseOrderItems.push({product: product, sku: sku, quantity: quantity});
        }

        this.setState({
            purchaseOrderItems: purchaseOrderItems
        });
    };

    onUpdatePurchaseOrderNumber = (newName) => {
        this.setState({
            purchaseOrderNumber: newName
        });
        return new Promise(function(resolve, reject) {
            resolve({ newFieldText: newName });
        }.bind(this));
    };

    onCompletePurchaseOrder = () => {
        n.notice("Marking the purchase order as complete.");
        this.completePurchaseOrderRequest = $.ajax({
            method: 'POST',
            data: {id: this.state.purchaseOrderId},
            url: '/products/purchaseOrders/complete',
            success: function (response) {
                if (! response.success) {
                    n.error("A problem occurred when attempting to complete the purchase order.");
                    return;
                }
                window.triggerEvent('purchaseOrderListRefresh');
                this.setState({
                    purchaseOrderStatus: COMPLETE_STATUS
                });
                n.success('Set the status of this purchase order to complete.');
            }.bind(this)
        });
    };

    onDownloadPurchaseOrder = () => {
        this.downloadPurchaseOrderRequest = $.ajax({
            method: 'POST',
            data: {id: this.state.purchaseOrderId},
            url: '/products/purchaseOrders/download',
            success: function (response) {
                var uri = 'data:text/csv;charset=utf-8;base64,' + btoa(response);

                var downloadLink = document.createElement("a");
                downloadLink.href = uri;
                downloadLink.download = "purchase_order.csv";

                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }
        });
    };

    onDeletePurchaseOrder = () => {
        n.notice('Deleting the purchase order.');
        this.deletePurchaseOrderRequest = $.ajax({
            method: 'POST',
            data: {id: this.state.purchaseOrderId},
            url: '/products/purchaseOrders/delete',
            success: function (response) {
                if (! response.success) {
                    n.error("A problem occurred when attempting to delete the purchase order.");
                    return;
                }
                window.triggerEvent('purchaseOrderListRefresh');
                this.resetEditor();
                n.success('Successfully deleted the purchase order.');
            }.bind(this)
        });
    };

    onSavePurchaseOrder = () => {
        n.notice('Saving the purchase order.');
        var url = '/products/purchaseOrders/create';
        if (this.state.purchaseOrderId > 0) {
            url = '/products/purchaseOrders/save';
        }
        this.savePurchaseOrderRequest = $.ajax({
            method: 'POST',
            data: {
                id: this.state.purchaseOrderId,
                externalId: this.state.purchaseOrderNumber,
                purchaseOrderItems: JSON.stringify(this.state.purchaseOrderItems)
            },
            url: url,
            success: function (response) {
                if (! response.success) {
                    n.error("A problem occurred when attempting to save the purchase order.");
                    return;
                }
                if (response.id) {
                    this.setState({
                        purchaseOrderId: response.id
                    });
                }
                window.triggerEvent('purchaseOrderListRefresh');
                n.success('Successfully saved the purchase order.');
            }.bind(this),
            error: function (response) {
                n.error("An error occurred when attempting to save the purchase order.");
            }
        });
    };

    onSkuChanged = (oldSku, selection) => {
        var newSku = selection.value;
        if (selection === undefined || oldSku === newSku) {
            return;
        }

        var oldSkuQuantity = 0;
        var purchaseOrderItems = this.state.purchaseOrderItems.slice();
        purchaseOrderItems.forEach(function (row) {
            if (row.sku === oldSku) {
                oldSkuQuantity = parseInt(row.quantity);
            }
        });

        var alreadyAddedToForm = purchaseOrderItems.find(function (row) {
            if (row.sku === newSku) {
                row.quantity += parseInt(oldSkuQuantity);
                return true;
            }
        });
        if (alreadyAddedToForm) {
            this.onRowRemove(oldSku);
            return;
        }
        this.updateItemRow(oldSku, 'sku', selection.value);
    };

    updateItemRow = (sku, key, value) => {
        var purchaseOrderItems = this.state.purchaseOrderItems.slice();
        purchaseOrderItems.forEach(function (row) {
            if (row.sku === sku) {
                row[key] = value;
            }
        });
        this.setState({
            purchaseOrderItems: purchaseOrderItems
        });
    };

    onStockQuantityUpdated = (sku, quantity) => {
        this.updateItemRow(sku, 'quantity', parseInt(quantity));
    };

    onRowRemove = (sku) => {
        var purchaseOrderItems = this.state.purchaseOrderItems.filter(function (row) {
            return row.sku !== sku;
        });

        this.setState({
            purchaseOrderItems: purchaseOrderItems
        });
    };

    completeButtonEnabled = () => {
        return this.state.purchaseOrderStatus !== COMPLETE_STATUS
            && this.state.purchaseOrderId
            && this.state.purchaseOrderId > 0
            && this.state.purchaseOrderItems
            && this.state.purchaseOrderItems.length > 0;
    };

    downloadButtonEnabled = () => {
        return this.state.purchaseOrderId
            && this.state.purchaseOrderId > 0;
    };

    deleteButtonEnabled = () => {
        return this.state.purchaseOrderId
            && this.state.purchaseOrderId > 0;
    };

    fetchProductsWithLowStock = () => {
        $.get('/products/purchaseOrders/fetchLowStockProducts', (data) => {
            this.fetchProductsBySkus(data.skus, true);
        });
    };

    fetchProductsBySupplier = (supplier) => {
        $.post('/products/purchaseOrders/fetchProductsForSupplier', {supplierId: supplier.id}).done((data) => {
            this.fetchProductsBySkus(data.skus);
        });
    };

    fetchProductsBySkus = (skus, lowStockProductsOnly = false) => {
        if (skus.length === 0) {
            n.notice('No products found', true, 4000);
            return;
        }

        n.success(`Found ${skus.length} products, please wait while we populate the new Purchase Order...`, true, 3000);

        let filter = new ProductFilter;
        filter.sku = skus;
        filter.limit = 500;
        filter.replaceVariationWithParent = true;
        filter.embedVariationsAsLinks = false;
        filter.embeddedDataToReturn = ['stock', 'variation', 'image'];
        AjaxHandler.fetchByFilter(filter, this.populateWithProducts.bind(this, lowStockProductsOnly));
    };

    populateWithProducts = (lowStockProductsOnly, response) => {
        let products = [];

        for (let product of response.products.slice()) {
            this.cleanupProductData(product);

            if (product.variationCount === 0) {
                products.push({
                    product: product,
                    sku: product.sku,
                    quantity: this.getQuantityForProduct(product)
                });
                continue;
            }

            let variations = this.getVariationsFromProduct(product, lowStockProductsOnly);
            products = products.concat(variations);
        }

        if (products.length === 0){
            return;
        }

        this.addItemRowMulti(products)
    };

    getQuantityForProduct = (product) => {
        const stock = product.stock;
        if (!stock) {
            return DEFAULT_REORDER_QUANTITY;
        }

        if (stock.reorderQuantity !== undefined && stock.reorderQuantity !== null) {
            return stock.reorderQuantity;
        }

        if (product.reorderQuantityDefault !== undefined && product.reorderQuantityDefault !== null) {
            return product.reorderQuantityDefault;
        }

        return DEFAULT_REORDER_QUANTITY;
    };

    getVariationsFromProduct = (product, lowStockProductsOnly) => {
        let variations = [];
        for (let variation of product.variations.slice()) {
            if (!variation.stock || (lowStockProductsOnly && !variation.stock.lowStockThresholdTriggered)) {
                continue;
            }
            this.cleanupProductData(variation);
            variations.push({
                product: product,
                sku: variation.sku,
                quantity: this.getQuantityForProduct(variation)
            });
        }
        return variations;
    };

    cleanupProductData = (product) => {
        delete product.imageIds;
        delete product.listingImageIds;
        delete product.taxRateIds;
        delete product.eTag;
        delete product.listings;
        delete product.listingsPerAccount;
        delete product.stockModeDefault;
        delete product.stockLevelDefault;
        delete product.lowStockThresholdDefault;
        delete product.taxRates;
    };

    render() {
        return (
            <EditorComponent
                editable={this.state.purchaseOrderStatus !== COMPLETE_STATUS}
                completeButtonEnabled={this.completeButtonEnabled()}
                downloadButtonEnabled={this.downloadButtonEnabled()}
                deleteButtonEnabled={this.deleteButtonEnabled()}
                onNameChange={this.onUpdatePurchaseOrderNumber}
                purchaseOrderNumber={this.state.purchaseOrderNumber}
                purchaseOrderItems={this.state.purchaseOrderItems}
                onCompleteClicked={this.onCompletePurchaseOrder}
                onDownloadClicked={this.onDownloadPurchaseOrder}
                onDeleteClicked={this.onDeletePurchaseOrder}
                onSaveClicked={this.onSavePurchaseOrder}
                onSkuChanged={this.onSkuChanged}
                onStockQuantityUpdated={this.onStockQuantityUpdated}
                onRowRemove={this.onRowRemove}
            />
        );
    }
}

export default EditorContainer;
