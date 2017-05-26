define([
    'react',
    'Common/Components/Popup',
    'Common/Components/ProductDropdown/Dropdown',
    'Common/Components/ItemRow'
], function(
    React,
    Popup,
    ProductDropdown,
    ItemRow
) {
    "use strict";

    var ProductLinkEditorComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initialProductLinks: []
            }
        },
        getInitialState: function () {
            return {
                productLinks: this.props.initialProductLinks
            };
        },
        componentWillReceiveProps: function (newProps) {
            console.log('Components received new props');
            if (newProps.initialProductLinks && newProps.initialProductLinks.length) {
                this.setState({
                    productLinks: newProps.initialProductLinks
                });
            }
        },
        componentDidMount: function() {
            window.addEventListener('productSelection', this.onProductSelected);
        },
        componentWillUnmount: function() {
            window.removeEventListener('productSelection', this.onProductSelected);
        },
        addProductLinkMulti: function (items) {
            var productLinks = this.state.productLinks.slice();

            items.forEach(function (item) {
                var alreadyAddedToForm = productLinks.find(function (row) {
                    if (row.sku === item.sku) {
                        row.quantity += parseInt(item.quantity);
                        return true;
                    }
                });
                if (! alreadyAddedToForm) {
                    //productLinks.push({product: item.product, sku: item.sku, quantity: item.quantity});
                }
            });

            this.setState({
                productLinks: productLinks
            });
        },
        addProductLink: function (product, sku, quantity) {
            var productLinks = this.state.productLinks.slice();

            var alreadyAddedToForm = productLinks.find(function (row) {
                if (row.sku === sku) {
                    row.quantity += parseInt(quantity);
                    return true;
                }
            });
            if (! alreadyAddedToForm) {
                productLinks.push({product: product, sku: sku, quantity: quantity});
            }

            this.setState({
                productLinks: productLinks
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
            var productLinks = this.state.productLinks.slice();
            productLinks.forEach(function (row) {
                if (row.sku === oldSku) {
                    oldSkuQuantity = parseInt(row.quantity);
                }
            });

            var alreadyAddedToForm = productLinks.find(function (row) {
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
            var productLinks = this.state.productLinks.slice();
            productLinks.forEach(function (row) {
                if (row.sku === sku) {
                    row[key] = value;
                }
            });
            this.setState({
                productLinks: productLinks
            });
        },
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateItemRow(sku, 'quantity', parseInt(quantity));
        },
        onRowRemove: function (sku) {
            var productLinks = this.state.productLinks.filter(function (row) {
                return row.sku !== sku;
            });

            this.setState({
                productLinks: productLinks
            });
        },
        render: function()
        {
            return (
                <Popup
                    initiallyActive={!!this.props.productLink.sku}
                    onYesButtonPressed={this.props.onYesButtonPressed}
                    onNoButtonPressed={this.props.onNoButtonPressed}
                    headerText={"Select products to link to "+this.props.productLink.sku}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                >
                    <div id="product-link-editor">
                        <p>
                            Once the products are linked this item will no longer have its own stock.
                            Instead its stock level will be calculated based on the available stock of the product it is linked to.
                        </p>
                        <div className="product-dropdown">
                            <ProductDropdown />
                        </div>
                        {this.state.productLinks.map(function (productLink) {
                            return (
                                <ItemRow row={productLink}
                                         onSkuChange={this.onSkuChanged}
                                         onStockQuantityUpdate={this.onStockQuantityUpdated}
                                         onRowRemove={this.onRowRemove}
                                />
                            );
                        }.bind(this))}
                    </div>
                </Popup>
            );
        }
    });

    return ProductLinkEditorComponent;
});