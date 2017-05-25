define([
    'react',
    'Common/Components/Popup',
], function(
    React,
    Popup
) {
    "use strict";

    var ProductLinkEditorComponent = React.createClass({
        getDefaultProps: function () {
            return {
                productName: ""
            }
        },
        render: function()
        {
            return (
                <Popup
                    initiallyActive={!!this.props.productName.length}
                    onYesButtonPressed={this.props.onYesButtonPressed}
                    onNoButtonPressed={this.props.onNoButtonPressed}
                    headerText={"Select products to link to "+this.props.productName}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                >
                    <div id="product-link-editor">
                        <p>
                            Once the products are linked this item will no longer have its own stock.
                            Instead its stock level will be calculated based on the available stock of the product it is linked to.
                        </p>
                        <ProductDropdown disabled={!this.props.editable} />
                        <div className="product-list" disabled={!this.props.editable}>
                            {this.props.purchaseOrderItems.map(function (row) {
                                return (
                                    <ItemRow row={row}
                                        disabled={!this.props.editable}
                                        onSkuChange={this.props.onSkuChanged}
                                        onStockQuantityUpdate={this.props.onStockQuantityUpdated}
                                        onRowRemove={this.props.onRowRemove}
                                    />
                                );
                            }.bind(this))}
                        </div>
                    </div>
                </Popup>
            );
        }
    });

    return ProductLinkEditorComponent;
});