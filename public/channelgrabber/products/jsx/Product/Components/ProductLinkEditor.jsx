define([
    'react',
    'Common/Components/Popup',
    'Common/Components/ProductDropdown/Dropdown',
    'Common/Components/Button',
    'Common/Components/ItemRow'
], function(
    React,
    Popup,
    ProductDropdown,
    Button,
    ItemRow
) {
    "use strict";

    var ProductLinkEditorComponent = React.createClass({
        getDefaultProps: function () {
            return {
                productLink: {
                    links: [],
                    sku: ""
                }
            }
        },
        getInitialState: function () {
            return {
                sku: this.props.productLink.sku,
                links: this.props.productLink.links,
                unlinkConfirmPopup: false
            };
        },
        componentWillReceiveProps: function (newProps) {
            this.setState({
                sku: newProps.productLink.sku,
                links: newProps.productLink.links
            });
        },
        componentDidMount: function() {
            window.addEventListener('productSelection', this.onProductSelected);
        },
        componentWillUnmount: function() {
            window.removeEventListener('productSelection', this.onProductSelected);
            this.saveProductLinksRequest.abort();
            this.unlinkProductLinksRequest.abort();
        },
        addProductLink: function (product, sku, quantity) {
            var links = this.state.links.slice();

            var alreadyAddedToForm = links.find(function (row) {
                if (row.sku === sku) {
                    row.quantity += parseInt(quantity);
                    return true;
                }
            });
            if (! alreadyAddedToForm) {
                links.push({product: product, sku: sku, quantity: quantity});
            }

            this.setState({
                links: links
            });
        },
        updateItemRow: function (sku, key, value) {
            var links = this.state.links.slice();
            links.forEach(function (row) {
                if (row.sku === sku) {
                    row[key] = value;
                }
            });
            this.setState({
                links: links
            });
        },
        onProductSelected: function (event) {
            var data = event.detail;
            this.addProductLink(data.product, data.sku, data.quantity);
        },
        onSkuChanged: function (oldSku, selection) {
            var newSku = selection.value;
            if (selection === undefined || oldSku === newSku) {
                return;
            }

            var oldSkuQuantity = 0;
            var links = this.state.links.slice();
            links.forEach(function (row) {
                if (row.sku === oldSku) {
                    oldSkuQuantity = parseInt(row.quantity);
                }
            });

            var alreadyAddedToForm = links.find(function (row) {
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
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateItemRow(sku, 'quantity', parseInt(quantity));
        },
        onRowRemove: function (sku) {
            var links = this.state.links.filter(function (row) {
                return row.sku !== sku;
            });

            this.setState({
                links: links
            });
        },
        onSaveProductLinks: function () {
            n.notice('Saving product links.');
            this.saveProductLinksRequest = $.ajax({
                'url' : "/products/links/save",
                'data' : {
                    sku: this.state.sku,
                    links: JSON.stringify(this.state.links)
                },
                'method' : 'POST',
                'dataType' : 'json',
                'success' : function (response) {
                    window.triggerEvent('productLinkRefresh');
                    this.setState(
                        {unlinkConfirmPopup: false},
                        function () {
                            this.onEditorReset();
                            this.props.fetchUpdatedStockLevels(this.state.sku);
                        }.bind(this)
                    );
                    n.success('Product links saved successfully.');
                }.bind(this),
                'error' : function (response) {
                    var error = JSON.parse(response.responseText);
                    n.error(error.message);
                }.bind(this)
            });
        },
        onUnlinkProductsClicked: function () {
            this.setState({
                unlinkConfirmPopup: true
            });
        },
        unlinkProducts: function () {
            n.notice('Removing product links.');
            this.unlinkProductLinksRequest = $.ajax({
                'url' : "/products/links/remove",
                'data' : {
                    sku: this.state.sku
                },
                'method' : 'POST',
                'dataType' : 'json',
                'success' : function (response) {
                    window.triggerEvent('productLinkRefresh');
                    this.setState(
                        {unlinkConfirmPopup: false},
                        function () {
                            this.onEditorReset();
                            this.props.fetchUpdatedStockLevels(this.state.sku);
                        }.bind(this)
                    );
                    n.success('Product links removed successfully.');
                }.bind(this),
                'error' : function (response) {
                    var error = JSON.parse(response.responseText);
                    n.error(error.message);
                }.bind(this)
            });
        },
        onEditorReset: function () {
            this.setState({
                sku: "",
                links: [],
                unlinkConfirmPopup: false
            });
            this.props.onEditorClose();
        },
        renderUnlinkButton: function () {
            if (this.props.productLink.links === undefined || this.props.productLink.links.length === 0) {
                return;
            }
            return (
                <div className="product-unlink-button">
                    <Popup initiallyActive={this.state.unlinkConfirmPopup}
                           className="unlink-popup"
                           onNoButtonPressed={function(){ this.setState({ unlinkConfirmPopup: false }); }.bind(this)}
                           onYesButtonPressed={this.unlinkProducts}
                    >
                        {"Please confirm you would like remove all product links from "+this.props.productLink.sku}
                    </Popup>
                    <Button text="Unlink Products" onClick={this.onUnlinkProductsClicked} sprite="sprite-linked-22-black"/>
                </div>
            );
        },
        render: function()
        {
            return (
                <Popup
                    initiallyActive={!!this.state.sku}
                    className="editor-popup"
                    onYesButtonPressed={this.onSaveProductLinks}
                    onNoButtonPressed={this.onEditorReset}
                    headerText={"Select products to link to "+this.props.productLink.sku}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                    onClickOutside={this.onEditorReset}
                >
                    <div id="product-link-editor">
                        <p>
                            Once the products are linked this item will no longer have its own stock.
                            Instead its stock level will be calculated based on the available stock of the product it is linked to.
                        </p>
                        <div className="product-dropdown">
                            <ProductDropdown skuThatProductsCantLinkFrom={this.props.productLink.sku} />
                        </div>
                        <div className="product-rows">
                            {this.state.links.map(function (productLink) {
                                return (
                                    <ItemRow row={productLink}
                                             onSkuChange={this.onSkuChanged}
                                             onStockQuantityUpdate={this.onStockQuantityUpdated}
                                             onRowRemove={this.onRowRemove}
                                    />
                                );
                            }.bind(this))}
                        </div>
                        {this.renderUnlinkButton()}
                    </div>
                </Popup>
            );
        }
    });

    return ProductLinkEditorComponent;
});