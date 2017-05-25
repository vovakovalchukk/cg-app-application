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
                productName: ""
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
            var linkedProducts = this.state.linkedProducts.slice();

            items.forEach(function (item) {
                var alreadyAddedToForm = linkedProducts.find(function (row) {
                    if (row.sku === item.sku) {
                        row.quantity += parseInt(item.quantity);
                        return true;
                    }
                });
                if (! alreadyAddedToForm) {
                    linkedProducts[].push({product: item.product, sku: item.sku, quantity: item.quantity});
                }
            });

            this.setState({
                linkedProducts: linkedProducts
            });
        },
        addProductLink: function (product, sku, quantity) {
            var linkedProducts = this.state.linkedProducts.slice();

            var alreadyAddedToForm = linkedProducts.find(function (row) {
                if (row.sku === sku) {
                    row.quantity += parseInt(quantity);
                    return true;
                }
            });
            if (! alreadyAddedToForm) {
                linkedProducts.push({product: product, sku: sku, quantity: quantity});
            }

            this.setState({
                linkedProducts: linkedProducts
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
                        <ProductDropdown />
                    </div>
                </Popup>
            );
        }
    });

    return ProductLinkEditorComponent;
});