define([
    'react',
    'Product/Components/Input',
    'Product/Components/Select'
], function(
    React,
    Input,
    Select
) {
    "use strict";
    var OrderTable = React.createClass({
        getDefaultProps: function () {
            return {
                orderRows: []
            }
        },
        getVariationSwitcherDropdown: function (product, thisSku) {
            if (! product.variations) {
                return;
            }
            var selectedOption = null;
            var options = product.variations.map(function (variation) {
                var option = {value: variation.sku, name: variation.sku};
                if (thisSku === variation.sku) {
                    selectedOption = option;
                }
                return option;
            });
            return <Select options={options} onNewOption={this.onSkuChanged} selectedOption={selectedOption}/>
        },
        onSkuChanged: function () {

        },
        getImageSource: function (variation) {
            var noProductImage = this.context.imageBasePath + '/noproductsimage.png';
            return variation.images.length > 0 ? variation.images[0]['url'] : noProductImage;
        },
        getVariationImageSource: function (orderRow) {
            var sku = orderRow.sku;

            if (! orderRow.product.variations) {
                return this.getImageSource(orderRow.product);
            }

            var variation = orderRow.product.variations.find(function (variation) {
                if (variation.sku === sku && variation.images.length > 0) {
                    return true;
                }
            });
            if (! variation) {
                return this.getImageSource(orderRow.product);
            }
            return this.getImageSource(variation);
        },
        onStockQuantityUpdated: function (sku, quantity) {
            console.log(sku);
            console.log(quantity);
        },
        getOrderRows: function () {
            return (
                this.props.orderRows.map(function (row) {
                    return (
                        <div className="order-row">
                            <div className="order-row-img">
                                <img src={this.getVariationImageSource(row)} />
                            </div>
                            <div className="order-row-description">
                                <div className="order-row-name">{row.product.name}</div>
                                <div className="order-row-sku">{row.sku}</div>
                            </div>
                            <div className="order-row-attributes">
                                {this.getVariationSwitcherDropdown(row.product, row.sku)}
                            </div>
                            <div className="order-row-qty-input">
                                <span className="multiplier">X</span>
                                <Input name='quantity' initialValue={row.quantity} submitCallback={this.onStockQuantityUpdated.bind(this, row.product.sku)} />
                            </div>
                        </div>
                    )
                }.bind(this))
            );
        },
        render: function () {
            return (
                <div className="order-table-wrapper">
                    <div className="order-rows-wrapper">
                        {this.getOrderRows()}
                    </div>
                    <div className="order-footer-wrapper">
                    </div>
                </div>
            );
        }
    });

    OrderTable.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return OrderTable;
});