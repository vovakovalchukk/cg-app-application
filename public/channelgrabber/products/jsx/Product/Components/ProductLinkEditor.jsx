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
                productLink: {}
            }
        },
        componentDidMount: function()
        {
            window.addEventListener('productSelection', this.onProductSelected);
        },
        componentWillUnmount: function()
        {
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
                    //productLinks[].push({product: item.product, sku: item.sku, quantity: item.quantity});
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
        render: function()
        {
            return (
                <Popup
                    initiallyActive={!!this.props.productLink.sku.length}
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
                        <ProductDropdown />
                    </div>
                </Popup>
            );
        }
    });

    return ProductLinkEditorComponent;
});