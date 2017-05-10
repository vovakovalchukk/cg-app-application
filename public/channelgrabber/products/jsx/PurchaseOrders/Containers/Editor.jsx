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
                />
            );
        }
    });

    return EditorContainer;
});