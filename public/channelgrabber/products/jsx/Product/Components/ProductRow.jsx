define([
    'react',
    'thenBy',
    'Product/Components/Checkbox',
    'Product/Components/Status',
    'Product/Components/VariationView',
    'Product/Components/Button',
    'Product/Components/Select',
    'Product/Components/Input',
    'Product/Components/SimpleTabs/Tabs',
    'Product/Components/SimpleTabs/Pane',
    'Product/Components/DimensionsView',
    'Product/Components/StockView',
    'Product/Components/VatView',
    'Product/Filter/Entity',
    'Product/Storage/Ajax'
], function(
    React,
    ThenBySort,
    Checkbox,
    Status,
    VariationView,
    Button,
    Select,
    Input,
    Tabs,
    Pane,
    DimensionsView,
    StockView,
    VatView,
    ProductFilter,
    AjaxHandler
) {
    "use strict";

    var ProductRowComponent = React.createClass({
        isParentProduct: function() {
            return this.props.product.variationCount !== undefined && this.props.product.variationCount >= 1
        },
        getProductVariationsView: function()
        {
            if (this.isParentProduct()) {
                return <VariationView parentProduct={this.props.product} onColumnSortClick={this.onColumnSortClick} variationsSort={this.state.variationsSort} attributeNames={this.props.product.attributeNames} variations={this.state.variations} fullView={this.state.expanded}/>;
            } else {
                return <VariationView variations={[this.props.product]} fullView={this.state.expanded}/>;
            }
        },
        getProductDetailsView: function ()
        {
            var products = [this.props.product];
            if (this.isParentProduct()) {
                products = this.state.variations;
            }
            return (
                <div className="details-layout-column">
                    <Tabs selected={0}>
                        <Pane label="Stock">
                            <StockView variations={products} fullView={this.state.expanded} onVariationDetailChanged={this.onVariationDetailChanged}/>
                        </Pane>
                        <Pane label="Dimensions">
                            <DimensionsView variations={products} fullView={this.state.expanded} onVariationDetailChanged={this.onVariationDetailChanged}/>
                        </Pane>
                        <Pane label="VAT">
                            <VatView parentProduct={this.props.product} fullView={this.state.expanded} onVatChanged={this.vatUpdated} variationCount={this.state.variations.length}/>
                        </Pane>
                    </Tabs>
                </div>
            );
        },
        getExpandVariationsButton: function()
        {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 2) {
                return <Button text={(this.state.expanded ? 'Contract' : 'Expand') + " Variations"} onClick={this.expandButtonClicked}/>
            }
        },
        getVariationsBulkActions: function () {
            if (this.isParentProduct()) {
                return (
                <div className="footer-row">
                    <div className="variations-layout-column">
                        <div className="variations-button-holder">
                            {this.getExpandVariationsButton()}
                        </div>
                        <div className="stocklog-link-holder">
                            {this.context.isAdmin ? <a href={"/products/stockLog/"+this.props.product.id}>History Log</a> : ''}
                        </div>
                    </div>
                    <div className="details-layout-column">
                        <table>
                            <tbody>
                            <tr>
                                <td className="product-stock-available"></td>
                                <td className="product-stock-allocated"></td>
                                <td className="product-stock-available"></td>
                                <td colSpan="2" className="product-stock-mode">{this.getBulkStockModeDropdown()}</td>
                                <td colSpan="1" className="product-stock-level">{this.getBulkStockLevelInput()}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                );
            }
        },
        getBulkStockModeDropdown: function () {
            if (this.state.variations.length > 0) {
                return <Select prefix="Set All" options={this.getStockModeOptions()} selectedOption={this.state.bulkStockMode} onNewOption={this.bulkUpdateStockMode}/>
            }
        },
        getBulkStockLevelInput: function () {
            if (this.state.variations.length > 0) {
                return <Input name='bulk-level' submitCallback={this.bulkUpdateStockLevel} disabled={this.shouldBulkLevelBeDisabled()} />
            }
        },
        shouldBulkLevelBeDisabled: function () {
            var disabledStockMode = 'all';
            return (
                (this.state.bulkStockMode.value === "" || this.state.bulkStockMode.value === "null" || this.state.bulkStockMode.value === disabledStockMode) &&
                (this.props.product.stockModeDefault === disabledStockMode || this.props.product.stockModeDefault === null)
            );
        },
        vatUpdated: function (taxRateId) {
            n.notice('Updating product tax rate.');
            $.ajax({
                url : '/products/taxRate',
                data : { productId: this.props.product.id, taxRateId: taxRateId, memberState: taxRateId.substring(0, 2) },
                method : 'POST',
                dataType : 'json',
                success : function(response) {
                    n.success('Product tax rate updated successfully.');
                },
                error : function(response) {
                    n.error("There was an error when attempting to update the product tax rate.");
                }
            });
        },
        expandButtonClicked: function (e) {
            this.setState({
                expanded: !this.state.expanded
            });

            if (this.state.variations.length <= 2)  {
                $('#products-loading-message').show();
                var filter = new ProductFilter(null, this.props.product.id);
                AjaxHandler.fetchByFilter(filter, function(data) {
                    this.sortVariations(this.state.variationsSort, data.products);
                    $('#products-loading-message').hide();
                }.bind(this));

            }
        },
        onColumnSortClick: function(attributeName) {
            var newVariationSort = this.state.variationsSort.slice();

            if (newVariationSort.length < 1) {
                newVariationSort.push({attribute: attributeName, ascending: true});
            } else {
                var attributeAlreadyExists = false;
                newVariationSort.forEach(function (sort, index) {
                    if (sort.attribute === attributeName) {
                        attributeAlreadyExists = true;
                        if (sort.ascending) {
                            newVariationSort[index].ascending = false;
                        } else {
                            newVariationSort.splice(index, 1);
                        }
                    }
                });

                if (! attributeAlreadyExists) {
                    newVariationSort.push({attribute: attributeName, ascending: true});
                }
            }
            this.setState({
                variationsSort: newVariationSort
            });
            this.sortVariations(newVariationSort);
        },
        sortVariations: function (newVariationSort, variations) {
            if (newVariationSort.length < 1) {
                return;
            }
            var newVariations = variations || this.state.variations.slice();
            var sortFunction = firstBy();
            newVariationSort.forEach(function (nextSort) {
                sortFunction = sortFunction.thenBy(function(v){
                    return v.attributeValues[nextSort.attribute] ? v.attributeValues[nextSort.attribute] : "";
                }, {ignoreCase: true, direction: (nextSort.ascending ? 1 : -1)});
            });

            this.setState({
                variations: newVariations.sort(sortFunction)
            });
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
            return this.state.variations[0].stock.stockLevel;
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
                    success: function(response) {
                        n.success('Bulk stock level updated successfully.');
                        resolve({ savedValue: response.level || 0 });
                        this.updateVariationsStockMode(response);
                    }.bind(this),
                    error: function(error) {
                        n.error("There was an error when attempting to bulk update the stock level.");
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        bulkUpdateStockMode: function(stockMode) {
            n.notice('Bulk updating stock mode for all variations.');
            $.ajax({
                url : '/products/stockMode',
                data : { id: this.props.product.id, stockMode: stockMode.value },
                method : 'POST',
                dataType : 'json',
                success : function(response) {
                    n.success('Bulk stock mode updated successfully.');
                    this.updateVariationsStockMode(response);
                    this.setState({
                        bulkStockMode: stockMode
                    });
                }.bind(this),
                error : function(response) {
                    n.error("There was an error when attempting to bulk update the stock mode.");
                }
            });
        },
        updateVariationsStockMode: function(stockModes) {
            var updatedVariations = this.state.variations.slice();
            updatedVariations.forEach(function(variation) {
                var stockMode = stockModes[variation.sku];
                var stockModeOption;
                variation.stockModeOptions.forEach(function (mode, stockModeIndex) {
                    variation.stockModeOptions[stockModeIndex].selected = (mode.value == stockMode.mode + "");
                    if (variation.stockModeOptions[stockModeIndex].selected) {
                        stockModeOption = mode;
                    }
                });
                variation.stockModeDesc = stockModeOption.title;
                variation.stock.stockMode = stockMode.mode;
                variation.stock.stockLevel = stockMode.level || 0;
                return variation;
            });
            this.setState({
                variations: updatedVariations
            });
        },
        onVariationDetailChanged: function(updatedVariation) {
            if (this.props.product.variationCount <= 1) {
                this.setState({
                    variations: [updatedVariation]
                });
                return;
            }
            var updatedVariations = this.state.variations.slice();

            updatedVariations.forEach(function (variation) {
                if (updatedVariation.sku === variation.sku) {
                    return updatedVariation;
                }
            });

            this.setState({
                variations: updatedVariations
            });
        },
        getInitialState: function () {
            return {
                expanded: false,
                variations: [],
                bulkStockMode: {
                    name: '',
                    value: ''
                },
                variationsSort: [
                    {
                        attribute: this.props.product.attributeNames[0],
                        ascending: true
                    }
                ]
            };
        },
        componentWillReceiveProps: function (newProps) {
            this.sortVariations(this.state.variationsSort, newProps.variations);
        },
        render: function()
        {
            return (
                <div className="product-container" id={"product-container-" + this.props.product.id}>
                    <input type="hidden" value={this.props.product.id} name="id" />
                    <input type="hidden" value={this.props.product.eTag} name={"product[" + this.props.product.id + "][eTag]"} />
                    <div className="product-info-container">
                        <div className="product-header">
                            <Checkbox id={this.props.product.id} />
                            <span className="product-title">{this.props.product.name}</span>
                            <Status listings={this.props.product.listings} />
                        </div>
                        <div className={"product-content-container" + (this.state.expanded ? "" : " contracted")}>
                            <div className="variations-layout-column">
                                {this.getProductVariationsView()}
                            </div>
                            {this.getProductDetailsView()}
                        </div>
                        <div className="product-footer">
                            {this.getVariationsBulkActions()}
                        </div>
                    </div>
                </div>
            );
        }
    });

    ProductRowComponent.contextTypes = {
        imageBasePath: React.PropTypes.string,
        isAdmin: React.PropTypes.bool
    };

    return ProductRowComponent;
});
