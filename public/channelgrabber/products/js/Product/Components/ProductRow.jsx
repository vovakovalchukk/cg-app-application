define([
    'react',
    'Product/Components/Checkbox',
    'Product/Components/Status',
    'Product/Components/VariationView',
    'Product/Components/DetailView',
    'Product/Components/Button',
    'Product/Components/Select',
    'Product/Components/Input',
    'Product/Filter/Entity',
    'Product/Storage/Ajax'
], function(
    React,
    Checkbox,
    Status,
    VariationView,
    DetailView,
    Button,
    Select,
    Input,
    ProductFilter,
    AjaxHandler
) {
    "use strict";

    var ProductRowComponent = React.createClass({
        getProductVariationsView: function()
        {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <VariationView attributeNames={this.props.product.attributeNames} variations={this.state.variations} fullView={this.state.expanded}/>;
            } else {
                return <VariationView variations={[this.props.product]} fullView={this.state.expanded}/>;
            }
        },
        getDetailsView: function ()
        {
            var products = [this.props.product];
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                products = this.state.variations;
            }
            return  <DetailView variations={products} fullView={this.state.expanded}/>
        },
        getExpandVariationsButton: function()
        {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <Button text={(this.state.expanded ? 'Contract' : 'Expand') + " Variations"} onClick={this.expandButtonClicked}/>
            }
        },
        getBulkStockModeDropdown: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <Select prefix="All" options={this.getStockModeOptions()} onNewOption={this.bulkUpdateStockMode}/>
            }
        },
        getBulkStockLevelInput: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <Input name='bulk-level' initialValue={this.getStockModeLevel()} submitCallback={this.bulkUpdateStockLevel} />
            }
        },
        getVatDropdowns: function () {
            return "";
        },
        expandButtonClicked: function (e) {
            e.preventDefault();

            if (this.state.variations.length <= 2)  {
                $('#products-loading-message').show();
                var filter = new ProductFilter(null, this.props.product.id);
                AjaxHandler.fetchByFilter(filter, function(data) {
                    this.setState({variations: data.products});
                    $('#products-loading-message').hide();
                }.bind(this));

            }
            this.setState({
                expanded: !this.state.expanded
            })
        },
        getInitialState: function () {
            return {
                expanded: false,
                variations: [],
                bulkStockMode: {
                    name: '',
                    value: ''
                }
            };
        },
        componentWillReceiveProps: function (newProps) {
            this.setState({
                variations: newProps.variations
            })
        },
        getStockModeOptions: function() {
            if (this.state.variations.length < 1) {
                return [];
            }
            var options = [];
            this.state.variations[0].stockModeOptions.map(function(option) {
                options.push({value: option.value, name: option.title});
            });
            return options;
        },
        getStockModeLevel: function () {
            if (this.state.variations.length < 1) {
                return;
            }
            var stockLevel = '';
            var stockLevelsSame = this.state.variations.reduce(function (element, nextElement) {
                if (element.stock.stockLevel === nextElement.stock.stockLevel) {
                    stockLevel = element.stock.stockLevel;
                    return true;
                }
            });
            if (stockLevelsSame) {
                return stockLevel;
            }
        },
        bulkUpdateStockLevel: function(name, value) {
            if (this.state.variations.length < 1) {
                return;
            }
            n.notice('Bulk updating stock level for all variations.');
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: 'products/stockLevel',
                    type: 'POST',
                    dataType : 'json',
                    data: {
                        id: this.props.product.id,
                        stockLevel: value
                    },
                    success: function() {
                        n.success('Bulk stock level updated successfully.');
                        resolve({ savedValue: value });
                    },
                    error: function(error) {
                        n.error(error);
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        bulkUpdateStockMode: function(stockMode) {
            n.notice('Bulk updating stock mode for all variations.');
            this.setState({
                bulkStockMode: stockMode
            });
            $.ajax({
                url : '/products/stockMode',
                data : { id: this.props.product.id, stockMode: stockMode.value },
                method : 'POST',
                dataType : 'json',
                success : function(response) {
                    n.success('Bulk stock mode updated successfully.');
                    this.updateVariationsStockMode(stockMode);
                }.bind(this),
                error : function(response) {
                    n.error(error);
                }
            });
        },
        updateVariationsStockMode: function(stockMode) {
            var updatedVariations = this.state.variations.map(function(variation) {
                variation.stockModeOptions.forEach(function (mode, stockModeIndex) {
                    if (mode.value === stockMode.value) {
                        variation.stockModeOptions[stockModeIndex].selected = true;
                    }
                });
                return variation;
            });
            this.setState({
                variations: updatedVariations
            });
        },
        render: function()
        {
            return (
                <div className="product-container" id={"product-container-" + this.props.product.id}>
                    <input type="hidden" value={this.props.product.id} name="id" />
                    <input type="hidden" value={this.props.product.eTag} name={"product[" + this.props.product.id + "][eTag]"} />
                    <Checkbox id={this.props.product.id} />
                    <div className="product-info-container">
                        <div className="product-header">
                            <span className="product-title">{this.props.product.name}</span>
                            <span className="product-sku">{this.props.product.sku}</span>
                            <Status listings={this.props.product.listings} />
                        </div>
                        <div className={"product-content-container" + (this.state.expanded ? "" : " contracted")}>
                            <div className="variations-layout-column">
                                {this.getProductVariationsView()}
                            </div>
                            {this.getDetailsView()}
                        </div>
                        <div className="product-footer">
                            <div className="footer-row">
                                <div className="variations-layout-column">
                                    <div className="variations-button-holder">
                                        {this.getExpandVariationsButton()}
                                    </div>
                                </div>
                                <div className="details-layout-column">
                                    <table>
                                        <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>{this.getBulkStockModeDropdown()}</td>
                                            <td>{this.getBulkStockLevelInput()}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div className="footer-row vat-row">
                                {this.getVatDropdowns()}
                            </div>
                        </div>
                    </div>
                </div>
            );
        }
    });

    ProductRowComponent.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return ProductRowComponent;
});