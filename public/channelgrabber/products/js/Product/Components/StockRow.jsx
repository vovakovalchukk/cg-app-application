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

    var StockRowComponent = React.createClass({
        getInitialState: function() {
            return   {
                stockMode: ''
            };
        },
        getValues: function(variation) {
            return [
                <td key="stock-available" className="product-stock-available">
                    <div>{(this.getOnHandStock() - Math.max(this.getAllocatedStock(), 0))}</div>
                </td>,
                <td key="stock-undispatched" className="product-stock-allocated">
                    <div>{this.getOnHandStock()}</div>
                </td>,
                <td key="stock-total" className="product-stock-available">
                    <Input name='total' value={this.getOnHandStock()} submitCallback={this.updateStockTotal}/>
                    <input type='hidden' value={variation.eTag} />
                    <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
                </td>,
                <td key="stock-mode" className="product-stock-mode">
                    <Select options={this.getStockModeOptions()} selected={this.getStockMode()}/>
                </td>,
                <td key="stock-level" className="product-stock-level">
                    <Input name='level' value={this.getOnHandStock()} submitCallback={this.updateFixLevel}/>
                </td>
            ];
        },
        getStockModeOptions: function() {
            if (!this.props.variation.stockModeOptions) {
                return [];
            }
            var options = [];
            this.props.variation.stockModeOptions.map(function(option) {
                options.push({value: option.value, name: option.title});
            });
            return options;
        },
        getStockMode: function() {
            if (!this.props.variation.stockModeOptions) {
                return [];
            }
            this.props.variation.stockModeOptions.map(function(option) {
                if (option.selected) {
                    return option.value;
                }
            });
        },
        getOnHandStock: function() {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].onHand : '');
        },
        getAllocatedStock: function() {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].allocated : '');
        },
        getStockEtag: function() {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].eTag : '');
        },
        getStockLocationId: function() {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].id : '');
        },
        updateStockTotal: function(name, value) {
            if (this.props.variation === null) {
                return;
            }
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: 'products/stock/update',
                    type: 'POST',
                    dataType : 'json',
                    data: {
                        stockLocationId: this.getStockLocationId(),
                        totalQuantity: value,
                        eTag: this.getStockEtag()
                    },
                    success: function() {
                        resolve({ savedValue: value });
                    },
                    error: function(error) {
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        updateFixLevel: function(name, value) {
            if (this.props.variation === null) {
                return;
            }
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: 'products/stockLevel',
                    type: 'POST',
                    dataType : 'json',
                    data: {
                        id: productId,
                        stockLevel: value
                    },
                    success: function() {
                        resolve({ savedValue: value });
                    },
                    error: function(error) {
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        getDefaultProps: function() {
            return {
                variation: null
            };
        },
        render: function () {
            return <tr>{this.getValues(this.props.variation)}</tr>;
        }
    });

    return StockRowComponent;
});