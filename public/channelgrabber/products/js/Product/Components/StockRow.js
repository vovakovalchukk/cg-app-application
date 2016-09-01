define(['react', 'Product/Components/Input', 'Product/Components/Select'], function (React, Input, Select) {
    "use strict";

    var StockRowComponent = React.createClass({
        displayName: 'StockRowComponent',

        getInitialState: function () {
            return {
                stockMode: {
                    name: '',
                    value: ''
                }
            };
        },
        getColumns: function (variation) {
            return [React.createElement(
                'td',
                { key: 'stock-available', className: 'product-stock-available' },
                React.createElement(
                    'div',
                    null,
                    this.getOnHandStock() - Math.max(this.getAllocatedStock(), 0)
                )
            ), React.createElement(
                'td',
                { key: 'stock-undispatched', className: 'product-stock-allocated' },
                React.createElement(
                    'div',
                    null,
                    this.getOnHandStock()
                )
            ), React.createElement(
                'td',
                { key: 'stock-total', className: 'product-stock-available' },
                React.createElement(Input, { name: 'total', initialValue: this.getOnHandStock(), submitCallback: this.updateStockTotal }),
                React.createElement('input', { type: 'hidden', value: variation.eTag }),
                React.createElement('input', { type: 'hidden', value: variation.stock ? variation.stock.locations[0].eTag : '' })
            ), React.createElement(
                'td',
                { key: 'stock-mode', className: 'product-stock-mode' },
                React.createElement(Select, { options: this.getStockModeOptions(), onNewOption: this.updateStockMode, defaultValue: this.props.variation.stockModeDesc })
            ), React.createElement(
                'td',
                { key: 'stock-level', className: 'product-stock-level' },
                React.createElement(Input, { name: 'level', initialValue: this.getStockModeLevel(), submitCallback: this.updateStockLevel, disabled: this.state.stockMode.value == null })
            )];
        },
        getStockModeLevel: function () {
            if (this.props.variation.stock && this.props.variation.stock.stockLevel) {
                return this.props.variation.stock.stockLevel;
            }
            return "";
        },
        getStockModeOptions: function () {
            if (!this.props.variation.stockModeOptions) {
                return [];
            }
            var options = [];
            this.props.variation.stockModeOptions.map(function (option) {
                options.push({ value: option.value, name: option.title, selected: option.selected });
            });
            return options;
        },
        getOnHandStock: function () {
            return this.props.variation.stock ? this.props.variation.stock.locations[0].onHand : '';
        },
        getAllocatedStock: function () {
            return this.props.variation.stock ? this.props.variation.stock.locations[0].allocated : '';
        },
        getStockEtag: function () {
            return this.props.variation.stock ? this.props.variation.stock.locations[0].eTag : '';
        },
        getStockLocationId: function () {
            return this.props.variation.stock ? this.props.variation.stock.locations[0].id : '';
        },
        updateStockMode: function (stockMode) {
            this.setState({
                stockMode: stockMode
            });
            n.notice('Updating stock mode.');
            $.ajax({
                url: '/products/stockMode',
                data: { id: this.props.variation.id, stockMode: stockMode.value },
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    n.success('Stock mode updated successfully..');
                    var modeUpdatedEvent = new CustomEvent('mode-' + this.props.variation.sku, { 'detail': { 'value': stockMode.value } });
                    window.dispatchEvent(modeUpdatedEvent);
                }.bind(this),
                error: function (error) {
                    n.error("There was an error when attempting to update the stock mode.");
                }
            });
        },
        updateStockTotal: function (name, value) {
            if (this.props.variation === null) {
                return;
            }
            n.notice('Updating stock total.');
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: 'products/stock/update',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        stockLocationId: this.getStockLocationId(),
                        totalQuantity: value,
                        eTag: this.getStockEtag()
                    },
                    success: function () {
                        n.success('Stock total updated successfully..');
                        var totalUpdatedEvent = new CustomEvent('total-' + this.props.variation.sku, { 'detail': { 'value': value } });
                        window.dispatchEvent(totalUpdatedEvent);
                        resolve({ savedValue: value });
                    }.bind(this),
                    error: function (error) {
                        n.error("There was an error when attempting to update the stock total.");
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        updateStockLevel: function (name, value) {
            if (this.props.variation === null) {
                return;
            }
            n.notice('Updating stock level.');
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: 'products/stockLevel',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: this.props.variation.id,
                        stockLevel: value
                    },
                    success: function () {
                        n.success('Stock level updated successfully..');
                        var levelUpdatedEvent = new CustomEvent('level-' + this.props.variation.sku, { 'detail': { 'value': value } });
                        window.dispatchEvent(levelUpdatedEvent);
                        resolve({ savedValue: value });
                    }.bind(this),
                    error: function (error) {
                        n.error("There was an error when attempting to update the stock level.");
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        getDefaultProps: function () {
            return {
                variation: null
            };
        },
        componentDidMount: function () {
            window.addEventListener('total-' + this.props.variation.sku, this.props.totalUpdated);
            window.addEventListener('level-' + this.props.variation.sku, this.props.levelUpdated);
            window.addEventListener('mode-' + this.props.variation.sku, this.props.modeUpdated);
        },
        componentWillUnmount: function () {
            window.removeEventListener('total-' + this.props.variation.sku, this.props.totalUpdated);
            window.removeEventListener('level-' + this.props.variation.sku, this.props.levelUpdated);
            window.removeEventListener('mode-' + this.props.variation.sku, this.props.modeUpdated);
        },
        render: function () {
            return React.createElement(
                'tr',
                null,
                this.getColumns(this.props.variation)
            );
        }
    });

    return StockRowComponent;
});
