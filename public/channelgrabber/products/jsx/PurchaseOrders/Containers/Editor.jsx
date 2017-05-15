define([
    'react',
    'PurchaseOrders/Components/Editor'
], function(
    React,
    EditorComponent
) {
    "use strict";

    var DEFAULT_PO_NUMBER = "Enter Purchase Order Number";

    var EditorContainer = React.createClass({
        getInitialState: function () {
            return {
                purchaseOrderNumber: DEFAULT_PO_NUMBER,
                productList: []
            };
        },
        componentDidMount: function () {
            window.addEventListener('createNewPurchaseOrder', this.resetEditor);
            window.addEventListener('purchaseOrderSelected', this.populateEditor);
            window.addEventListener('productSelection', this.onProductSelected);
        },
        componentWillUnmount: function () {
            window.removeEventListener('createNewPurchaseOrder', this.resetEditor);
            window.removeEventListener('purchaseOrderSelected', this.populateEditor);
            window.removeEventListener('productSelection', this.onProductSelected);
            this.completePurchaseOrderRequest.abort();
            this.downloadPurchaseOrderRequest.abort();
            this.deletePurchaseOrderRequest.abort();
            this.savePurchaseOrderRequest.abort();
        },
        populateEditor: function (event) {
            if (! event.detail) {
                return;
            }
            var purchaseOrder = event.detail;
            this.setState({
                purchaseOrderNumber: purchaseOrder.number,
                productList: purchaseOrder.list ? purchaseOrder.list : []
            });
        },
        resetEditor: function () {
            this.setState({
                purchaseOrderNumber: DEFAULT_PO_NUMBER,
                productList: []
            });
        },
        onProductSelected: function (e) {
            var data = e.detail;
            this.addItemRow(data.product, data.sku, data.quantity);
        },
        addItemRow: function (product, sku, quantity) {
            var productList = this.state.productList.slice();

            var alreadyAddedToForm = productList.find(function (row) {
                if (row.sku === sku) {
                    row.quantity += parseInt(quantity);
                    return true;
                }
            });
            if (! alreadyAddedToForm) {
                productList.push({product: product, sku: sku, quantity: quantity});
            }

            this.setState({
                productList: productList
            });
        },
        onUpdatePurchaseOrderNumber: function(newName) {
            this.setState({
                purchaseOrderNumber: newName
            });
            return new Promise(function(resolve, reject) {
                resolve({ newFieldText: newName });
            }.bind(this));
        },
        onCompletePurchaseOrder: function () {
            /**
             * initiate complete PO ajax request
             * trigger purchase order list refresh
             */
            this.completePurchaseOrderRequest = $.ajax({
                method: 'POST',
                url: '/products/purchaseOrders/complete',
                success: function (response) {
                    if (! response.success) {
                        n.error(response.error);
                        return;
                    }
                    n.success('Set the status of this purchase order to complete.');
                }
            });
        },
        onDownloadPurchaseOrder: function () {
            /**
             * initiate download PO ajax request
             */
            this.downloadPurchaseOrderRequest = $.ajax({
                method: 'POST',
                url: '/products/purchaseOrders/download',
                success: function (response) {
                    if (! response.success) {
                        n.error(response.error);
                        return;
                    }
                    n.success('Initiated download request for this purchase order.');
                }
            });
        },
        onDeletePurchaseOrder: function () {
            /**
             * initiate delete PO ajax request
             * trigger purchase order list refresh
             */
            this.deletePurchaseOrderRequest = $.ajax({
                method: 'POST',
                url: '/products/purchaseOrders/delete',
                success: function (response) {
                    if (! response.success) {
                        n.error(response.error);
                        return;
                    }
                    n.success('Successfully deleted the purchase order.');
                }
            });
        },
        onSavePurchaseOrder: function () {
            /**
             * initiate save PO ajax request
             * trigger purchase order list refresh
             */
            this.savePurchaseOrderRequest = $.ajax({
                method: 'POST',
                url: '/products/purchaseOrders/save',
                success: function (response) {
                    if (! response.success) {
                        n.error(response.error);
                        return;
                    }
                    n.success('Successfully saved the purchase order.');
                }
            });
        },
        onSkuChanged: function (oldSku, selection) {
            var newSku = selection.value;
            if (selection === undefined || oldSku === newSku) {
                return;
            }

            var oldSkuQuantity = 0;
            var productList = this.state.productList.slice();
            productList.forEach(function (row) {
                if (row.sku === oldSku) {
                    oldSkuQuantity = parseInt(row.quantity);
                }
            });

            var alreadyAddedToForm = productList.find(function (row) {
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
        },
        updateItemRow: function (sku, key, value) {
            var productList = this.state.productList.slice();
            productList.forEach(function (row) {
                if (row.sku === sku) {
                    row[key] = value;
                }
            });
            this.setState({
                productList: productList
            });
        },
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateItemRow(sku, 'quantity', parseInt(quantity));
        },
        onRowRemove: function (sku) {
            var productList = this.state.productList.filter(function (row) {
                return row.sku !== sku;
            });

            this.setState({
                productList: productList
            });
        },
        render: function()
        {
            return (
                <EditorComponent
                    onNameChange={this.onUpdatePurchaseOrderNumber}
                    purchaseOrderNumber={this.state.purchaseOrderNumber}
                    productList={this.state.productList}
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
    });

    return EditorContainer;
});