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
                purchaseOrderId: 0,
                purchaseOrderNumber: DEFAULT_PO_NUMBER,
                purchaseOrderItems: []
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
            if (purchaseOrder.items.length) {
                purchaseOrder.items.forEach(function (item) {
                    this.addItemRow(item.product, item.sku, item.quantity);
                }.bind(this));
            }
            this.setState({
                purchaseOrderId: purchaseOrder.id,
                purchaseOrderNumber: purchaseOrder.externalId,
                purchaseOrderItems: purchaseOrder.items ? purchaseOrder.items : []
            });
        },
        resetEditor: function () {
            this.setState({
                purchaseOrderNumber: DEFAULT_PO_NUMBER,
                purchaseOrderItems: []
            });
        },
        onProductSelected: function (e) {
            var data = e.detail;
            this.addItemRow(data.product, data.sku, data.quantity);
        },
        addItemRow: function (product, sku, quantity) {
            var purchaseOrderItems = this.state.purchaseOrderItems.slice();

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

            this.completePurchaseOrderRequest = $.ajax({
                method: 'POST',
                data: {id: this.state.purchaseOrderId},
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

            this.downloadPurchaseOrderRequest = $.ajax({
                method: 'POST',
                data: {id: this.state.purchaseOrderId},
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

            this.deletePurchaseOrderRequest = $.ajax({
                method: 'POST',
                data: {id: this.state.purchaseOrderId},
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

            var url = '/products/purchaseOrders/create';
            if (this.state.purchaseOrderId > 0) {
                url = '/products/purchaseOrders/save';
            }
            this.savePurchaseOrderRequest = $.ajax({
                method: 'POST',
                data: {
                    id: this.state.purchaseOrderId,
                    number: this.state.purchaseOrderNumber,
                    products: JSON.stringify(this.state.purchaseOrderItems)
                },
                url: url,
                success: function (response) {
                    if (! response.success) {
                        n.error(response.error);
                        return;
                    }
                    if (response.id) {
                        this.setState({
                            id: response.id
                        });
                    }
                    n.success('Successfully saved the purchase order.');
                }.bind(this)
            });
        },
        onSkuChanged: function (oldSku, selection) {
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
        },
        updateItemRow: function (sku, key, value) {
            var purchaseOrderItems = this.state.purchaseOrderItems.slice();
            purchaseOrderItems.forEach(function (row) {
                if (row.sku === sku) {
                    row[key] = value;
                }
            });
            this.setState({
                purchaseOrderItems: purchaseOrderItems
            });
        },
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateItemRow(sku, 'quantity', parseInt(quantity));
        },
        onRowRemove: function (sku) {
            var purchaseOrderItems = this.state.purchaseOrderItems.filter(function (row) {
                return row.sku !== sku;
            });

            this.setState({
                purchaseOrderItems: purchaseOrderItems
            });
        },
        render: function()
        {
            return (
                <EditorComponent
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
    });

    return EditorContainer;
});