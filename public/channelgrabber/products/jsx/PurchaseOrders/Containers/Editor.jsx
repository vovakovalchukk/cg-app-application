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
        render: function()
        {
            return (
                <EditorComponent
                    onNameChange={this.onUpdatePurchaseOrderNumber}
                    purchaseOrderNumber={this.state.purchaseOrderNumber}
                    productList={this.state.productList}
                />
            );
        }
    });

    return EditorContainer;
});