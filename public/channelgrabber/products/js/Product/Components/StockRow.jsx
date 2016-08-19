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
        getValues: function(variation) {
            return [
                <td key="stock-available" className="product-stock-available">
                    <div>{(this.getOnHandStock(variation) - Math.max(this.getAllocatedStock(variation), 0))}</div>
                </td>,
                <td key="stock-undispatched" className="product-stock-allocated">
                    <div>{this.getOnHandStock(variation)}</div>
                </td>,
                <td key="stock-total" className="product-stock-available">
                    <Input name='total' value={this.getOnHandStock(variation)} submitCallback={this.updateStockTotal}/>
                    <input type='hidden' value={variation.eTag} />
                    <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
                </td>,
                <td key="stock-mode" className="product-stock-mode">
                    Dropdown
                </td>,
                <td key="stock-level" className="product-stock-level">
                    <Input name='level' value={this.getOnHandStock(variation)} submitCallback={this.updateFixLevel}/>
                </td>
            ];
        },
        getStockModeOptions: function() {
            return [
                {
                    testA: 'test1'
                }, {
                    testB: 'test2'
                }, {
                    testC: 'test3'
                }
            ];
        },
        getOnHandStock: function(variation) {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].onHand : '');
        },
        getAllocatedStock: function(variation) {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].allocated : '');
        },
        getStockEtag: function(variation) {
            return (this.props.variation.stock ? this.props.variation.stock.locations[0].eTag : '');
        },
        getStockLocationId: function(variation) {
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