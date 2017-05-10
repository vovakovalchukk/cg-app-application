define([
    'react',
    'PurchaseOrders/Components/Editor'
], function(
    React,
    EditorComponent
) {
    "use strict";

    var EditorContainer = React.createClass({
        getInitialState: function () {
            return {
                purchaseOrderNumber: "",
                productList: []
            };
        },
        componentDidMount: function () {
            window.addEventListener('productSelection', this.onProductSelected);
        },
        componentWillUnmount: function () {
            window.removeEventListener('productSelection', this.onProductSelected);
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
                productList.push({product: product, sku: sku, quantity: quantity, price: 0});
            }

            this.setState({
                productList: productList
            });
        },
        onUpdatePurchaseOrderNumber: function(newName) {
            return new Promise(function(resolve, reject) {
                resolve({ newFieldText: newName });
            }.bind(this));
        },
        onCompletePurchaseOrder: function () {
            /**
             * initiate complete PO ajax request
             * trigger purchase order list refresh
             */
        },
        onDownloadPurchaseOrder: function () {
            /**
             * initiate download PO ajax request
             */
        },
        onDeletePurchaseOrder: function () {
            /**
             * initiate delete PO ajax request
             * trigger purchase order list refresh
             */
        },
        onSavePurchaseOrder: function () {
            /**
             * initiate save PO ajax request
             * trigger purchase order list refresh
             */
        },
        onSkuChanged: function () {
            /**
             * update productList
             */
        },
        onStockQuantityUpdated: function () {
            /**
             * update productList
             */
        },
        onRowRemove: function () {
            /**
             * update productList
             */
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