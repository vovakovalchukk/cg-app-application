import React from 'react';
import EditableFieldWithSubmit from 'Common/Components/EditableFieldWithSubmit';
import Button from 'Common/Components/Button';
import ProductDropdown from 'Product/Components/ProductDropdown/Dropdown';
import ItemRow from 'Common/Components/ItemRow';
    

    export default class extends React.Component {
        render() {
            return (
                <div className="purchase-orders-editor">
                    <div className="editor-row">
                        <EditableFieldWithSubmit disabled={!this.props.editable} initialFieldText={this.props.purchaseOrderNumber} onSubmit={this.props.onNameChange}/>
                    </div>
                    <div className="editor-row">
                        <Button disabled={!this.props.completeButtonEnabled} onClick={this.props.onCompleteClicked} sprite="sprite-complete-22-black" text="Complete"/>
                        <Button disabled={!this.props.downloadButtonEnabled} onClick={this.props.onDownloadClicked} sprite="sprite-download-22-black" text="Download"/>
                        <Button disabled={!this.props.deleteButtonEnabled} onClick={this.props.onDeleteClicked} sprite="sprite-cancel-22-black" text="Delete"/>
                        <Button disabled={!this.props.editable} onClick={this.props.onSaveClicked} sprite="sprite-save-22-black" text="Save"/>
                    </div>
                    <ProductDropdown disabled={!this.props.editable} />
                    <div className="product-list" disabled={!this.props.editable}>
                        {this.props.purchaseOrderItems.map(function (row) {
                            return (
                                <ItemRow row={row}
                                    disabled={!this.props.editable}
                                    onSkuChange={this.props.onSkuChanged}
                                    onStockQuantityUpdate={this.props.onStockQuantityUpdated}
                                    onRowRemove={this.props.onRowRemove}
                                    showStockColumn={true}
                                />
                            );
                        }.bind(this))}
                        </div>
                </div>
            );
        }
    }
