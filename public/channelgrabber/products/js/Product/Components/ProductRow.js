define(['react', 'thenBy', 'Product/Components/Checkbox', 'Product/Components/Status', 'Product/Components/VariationView', 'Product/Components/Button', 'Product/Components/Select', 'Product/Components/Input', 'Product/Components/SimpleTabs/Tabs', 'Product/Components/SimpleTabs/Pane', 'Product/Components/DimensionsView', 'Product/Components/StockView', 'Product/Filter/Entity', 'Product/Storage/Ajax'], function (React, ThenBySort, Checkbox, Status, VariationView, Button, Select, Input, Tabs, Pane, DimensionsView, StockView, ProductFilter, AjaxHandler) {
    "use strict";

    var ProductRowComponent = React.createClass({
        displayName: 'ProductRowComponent',

        getProductVariationsView: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return React.createElement(VariationView, { onColumnSortClick: this.onColumnSortClick, variationsSort: this.state.variationsSort, attributeNames: this.props.product.attributeNames, variations: this.state.variations, fullView: this.state.expanded });
            } else {
                return React.createElement(VariationView, { variations: [this.props.product], fullView: this.state.expanded });
            }
        },
        getProductDetailsView: function () {
            var products = [this.props.product];
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                products = this.state.variations;
            }
            return React.createElement(
                'div',
                { className: 'details-layout-column' },
                React.createElement(
                    Tabs,
                    { selected: 0 },
                    React.createElement(
                        Pane,
                        { label: 'Stock' },
                        React.createElement(StockView, { variations: products, fullView: this.state.expanded, onVariationDetailChanged: this.onVariationDetailChanged })
                    ),
                    React.createElement(
                        Pane,
                        { label: 'Dimensions' },
                        React.createElement(DimensionsView, { variations: products, fullView: this.state.expanded, onVariationDetailChanged: this.onVariationDetailChanged })
                    )
                )
            );
        },
        getExpandVariationsButton: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return React.createElement(Button, { text: (this.state.expanded ? 'Contract' : 'Expand') + " Variations", onClick: this.expandButtonClicked });
            }
        },
        getVariationsBulkActions: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return React.createElement(
                    'div',
                    { className: 'footer-row' },
                    React.createElement(
                        'div',
                        { className: 'variations-layout-column' },
                        React.createElement(
                            'div',
                            { className: 'variations-button-holder' },
                            this.getExpandVariationsButton()
                        )
                    ),
                    React.createElement(
                        'div',
                        { className: 'details-layout-column' },
                        React.createElement(
                            'table',
                            null,
                            React.createElement(
                                'tbody',
                                null,
                                React.createElement(
                                    'tr',
                                    null,
                                    React.createElement('td', null),
                                    React.createElement('td', null),
                                    React.createElement('td', null),
                                    React.createElement(
                                        'td',
                                        null,
                                        this.getBulkStockModeDropdown()
                                    ),
                                    React.createElement(
                                        'td',
                                        null,
                                        this.getBulkStockLevelInput()
                                    )
                                )
                            )
                        )
                    )
                );
            }
        },
        getBulkStockModeDropdown: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return React.createElement(Select, { prefix: 'All', options: this.getStockModeOptions(), onNewOption: this.bulkUpdateStockMode });
            }
        },
        getBulkStockLevelInput: function () {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return React.createElement(Input, { name: 'bulk-level', initialValue: this.getStockModeLevel(), submitCallback: this.bulkUpdateStockLevel });
            }
        },
        getVatDropdowns: function () {
            if (this.props.product.taxRates) {
                var showCodeInLabel = Object.keys(this.props.product.taxRates).length > 1;
                var vatDropdowns = [];

                for (var memberState in this.props.product.taxRates) {
                    if (!this.props.product.taxRates.hasOwnProperty(memberState)) {
                        continue;
                    }
                    var options = [];
                    for (var taxRateId in this.props.product.taxRates[memberState]) {
                        if (!this.props.product.taxRates[memberState].hasOwnProperty(taxRateId)) {
                            continue;
                        }
                        var formattedRate = parseFloat(this.props.product.taxRates[memberState][taxRateId]['rate']);
                        options.push({
                            'name': formattedRate + '% (' + this.props.product.taxRates[memberState][taxRateId]['name'] + ')',
                            'value': taxRateId,
                            'selected': this.props.product.taxRates[memberState][taxRateId]['selected']
                        });
                    }
                    vatDropdowns.push(React.createElement(Select, { prefix: showCodeInLabel ? memberState + ' VAT' : "VAT", options: options, onNewOption: this.vatUpdated }));
                }
                return vatDropdowns;
            }
        },
        vatUpdated: function (selection) {
            n.notice('Updating product tax rate.');
            $.ajax({
                url: '/products/taxRate',
                data: { productId: this.props.product.id, taxRateId: selection.value, memberState: selection.value.substring(0, 2) },
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    n.success('Product tax rate updated successfully.');
                },
                error: function (response) {
                    n.error("There was an error when attempting to update the product tax rate.");
                }
            });
        },
        expandButtonClicked: function (e) {
            this.setState({
                expanded: !this.state.expanded
            });

            if (this.state.variations.length <= 2) {
                $('#products-loading-message').show();
                var filter = new ProductFilter(null, this.props.product.id);
                AjaxHandler.fetchByFilter(filter, function (data) {
                    this.sortVariations(this.state.variationsSort, data.products);
                    $('#products-loading-message').hide();
                }.bind(this));
            }
        },
        onColumnSortClick: function (attributeName) {
            var newVariationSort = this.state.variationsSort.slice();

            if (newVariationSort.length < 1) {
                newVariationSort.push({ attribute: attributeName, ascending: true });
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

                if (!attributeAlreadyExists) {
                    newVariationSort.push({ attribute: attributeName, ascending: true });
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
                sortFunction = sortFunction.thenBy(function (v) {
                    return v.attributeValues[nextSort.attribute];
                }, { ignoreCase: true, direction: nextSort.ascending ? 1 : -1 });
            });

            this.setState({
                variations: newVariations.sort(sortFunction)
            });
        },
        getStockModeOptions: function () {
            if (this.state.variations.length < 1) {
                return [];
            }
            var options = [];
            this.state.variations[0].stockModeOptions.map(function (option) {
                options.push({ value: option.value, name: option.title });
            });
            return options;
        },
        getStockModeLevel: function () {
            if (this.state.variations.length < 1) {
                return;
            }
            return this.state.variations[0].stockLevel;
        },
        bulkUpdateStockLevel: function (name, value) {
            if (this.state.variations.length < 1) {
                return;
            }
            n.notice('Bulk updating stock level for all variations.');
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: 'products/stockLevel',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: this.props.product.id,
                        stockLevel: value
                    },
                    success: function () {
                        n.success('Bulk stock level updated successfully.');
                        resolve({ savedValue: value });
                    },
                    error: function (error) {
                        n.error("There was an error when attempting to bulk update the stock level.");
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        bulkUpdateStockMode: function (stockMode) {
            n.notice('Bulk updating stock mode for all variations.');
            this.setState({
                bulkStockMode: stockMode
            });
            $.ajax({
                url: '/products/stockMode',
                data: { id: this.props.product.id, stockMode: stockMode.value },
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    n.success('Bulk stock mode updated successfully.');
                    this.updateVariationsStockMode(stockMode);
                }.bind(this),
                error: function (response) {
                    n.error("There was an error when attempting to bulk update the stock mode.");
                }
            });
        },
        updateVariationsStockMode: function (stockMode) {
            var updatedVariations = this.state.variations.map(function (variation) {
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
        onVariationDetailChanged: function (updatedVariation) {
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
                variationsSort: [{
                    attribute: this.props.product.attributeNames[0],
                    ascending: true
                }]
            };
        },
        componentWillReceiveProps: function (newProps) {
            this.sortVariations(this.state.variationsSort, newProps.variations);
        },
        render: function () {
            return React.createElement(
                'div',
                { className: 'product-container', id: "product-container-" + this.props.product.id },
                React.createElement('input', { type: 'hidden', value: this.props.product.id, name: 'id' }),
                React.createElement('input', { type: 'hidden', value: this.props.product.eTag, name: "product[" + this.props.product.id + "][eTag]" }),
                React.createElement(Checkbox, { id: this.props.product.id }),
                React.createElement(
                    'div',
                    { className: 'product-info-container' },
                    React.createElement(
                        'div',
                        { className: 'product-header' },
                        React.createElement(
                            'span',
                            { className: 'product-title' },
                            this.props.product.name
                        ),
                        React.createElement(
                            'span',
                            { className: 'product-sku' },
                            this.props.product.sku
                        ),
                        React.createElement(Status, { listings: this.props.product.listings })
                    ),
                    React.createElement(
                        'div',
                        { className: "product-content-container" + (this.state.expanded ? "" : " contracted") },
                        React.createElement(
                            'div',
                            { className: 'variations-layout-column' },
                            this.getProductVariationsView()
                        ),
                        this.getProductDetailsView()
                    ),
                    React.createElement(
                        'div',
                        { className: 'product-footer' },
                        this.getVariationsBulkActions(),
                        React.createElement(
                            'div',
                            { className: 'footer-row vat-row' },
                            this.getVatDropdowns()
                        )
                    )
                )
            );
        }
    });

    ProductRowComponent.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return ProductRowComponent;
});
