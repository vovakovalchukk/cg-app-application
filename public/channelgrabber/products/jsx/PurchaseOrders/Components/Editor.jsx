define([
    'react',
    'Common/Components/EditableField',
    'Common/Components/Button',
    'Common/Components/ProductDropdown/Dropdown',
    'Common/Components/ItemRow'
], function(
    React,
    EditableField,
    Button,
    ProductDropdown,
    ItemRow
) {
    "use strict";

    var EditorComponent = React.createClass({
        render: function()
        {
            return (
                <div className="purchase-orders-editor">
                    <div className="editor-row">
                        <EditableField initialFieldText={this.props.purchaseOrderNumber} onSubmit={this.props.onNameChange}/>
                    </div>
                    <div className="editor-row">
                        <Button onClick={this.props.onCompleteClicked} sprite="sprite-complete-22-black" text="Complete"/>
                        <Button onClick={this.props.onDownloadClicked} sprite="sprite-download-22-black" text="Download"/>
                        <Button onClick={this.props.onDeleteClicked} sprite="sprite-cancel-22-black" text="Delete"/>
                        <Button onClick={this.props.onSaveClicked} sprite="sprite-save-22-black" text="Save"/>
                    </div>
                    <ProductDropdown />
                    <div className="product-list">
                        {this.props.productList.map(function (row) {
                            return (
                                <ItemRow row={row}
                                    onSkuChange={this.props.onSkuChanged}
                                    onStockQuantityUpdate={this.props.onStockQuantityUpdated}
                                    onRowRemove={this.props.onRowRemove}
                                />
                            );
                        }.bind(this))}
                        </div>
                </div>
            );
        }
    });

    return EditorComponent;
});