define([
    'react',
    'PurchaseOrders/Components/Editor'
], function(
    React,
    EditorComponent
) {
    "use strict";

    var COMPLETE_STATUS = "Complete";
    var DEFAULT_PO_STATUS = "In Progress";
    var DEFAULT_PO_NUMBER = "Enter Purchase Order Number";

    var EditorContainer = React.createClass({
        getInitialState: function () {
            return {
                purchaseOrderId: 0,
                purchaseOrderStatus: DEFAULT_PO_STATUS,
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
            this.resetEditor(function() {
                var purchaseOrder = event.detail;
                if (purchaseOrder.items && purchaseOrder.items.length) {
                    this.addItemRowMulti(purchaseOrder.items);
                }
                this.setState({
                    purchaseOrderId: purchaseOrder.id,
                    purchaseOrderStatus: purchaseOrder.status,
                    purchaseOrderNumber: purchaseOrder.externalId
                });
            }.bind(this));
        },
        resetEditor: function (afterResetCallback) {
            this.setState({
                purchaseOrderNumber: DEFAULT_PO_NUMBER,
                purchaseOrderStatus: DEFAULT_PO_STATUS,
                purchaseOrderId: 0,
                purchaseOrderItems: []
            }, afterResetCallback);
        },
        onProductSelected: function (e) {
            var data = e.detail;
            this.addItemRow(data.product, data.sku, data.quantity);
        },
        addItemRowMulti: function (items) {
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
                purchaseOrderItems: purchaseOrderItems
            });
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
        },
        onDownloadPurchaseOrder: function () {

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
        },
        onDeletePurchaseOrder: function () {

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
                }
            });
        },
        onSavePurchaseOrder: function () {
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
                    products: JSON.stringify(this.state.purchaseOrderItems)
                },
                url: url,
                success: function (response) {
                    if (! response.success) {
                        n.error("A problem occurred when attempting to save the purchase order.");
                        return;
                    }
                    if (response.id) {
                        this.setState({
                            id: response.id
                        });
                    }
                    window.triggerEvent('purchaseOrderListRefresh');
                    n.success('Successfully saved the purchase order.');
                }.bind(this),
                error: function (response) {
                    n.error("An error occurred when attempting to save the purchase order.");
                }
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
                    editable={this.state.purchaseOrderStatus !== COMPLETE_STATUS}
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