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
                stockMode: {
                    name: '',
                    value: ''
                }
            };
        },
        getColumns: function(variation) {
            return [
                <td key="stock-available" className="product-stock-available">
                    <div>{(this.getOnHandStock() - Math.max(this.getAllocatedStock(), 0))}</div>
                </td>,
                <td key="stock-undispatched" className="product-stock-allocated">
                    <div>{this.getOnHandStock()}</div>
                </td>,
                <td key="stock-total" className="product-stock-available">
                    <Input name='total' initialValue={this.getOnHandStock()} submitCallback={this.updateStockTotal}/>
                    <input type='hidden' value={variation.eTag} />
                    <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
                </td>,
                <td key="stock-mode" className="product-stock-mode">
                    <Select options={this.getStockModeOptions()} initialSelected={this.getStockMode()} onNewOption={this.updateStockMode}/>
                </td>,
                <td key="stock-level" className="product-stock-level">
                    <Input name='level' initialValue={this.getStockModeLevel()} submitCallback={this.updateStockLevel} disabled={this.state.stockMode.value == null} />
                </td>
            ];
        },
        getStockModeLevel: function() {
            if (this.props.variation.stock && this.props.variation.stock.stockLevel) {
                return this.props.variation.stock.stockLevel;
            }
            return "";
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
            var selectedStockMode = null;
            this.props.variation.stockModeOptions.map(function(option) {
                if (option.selected) {
                    selectedStockMode = {value: option.value, name: option.title};
                }
            });
            if (selectedStockMode === null) {
                selectedStockMode = {value: this.props.variation.stockModeOptions[0].value, name: this.props.variation.stockModeOptions[0].title};
            }
            return selectedStockMode;
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
        updateStockMode: function(stockMode) {
            this.setState({
                stockMode: stockMode
            });
            $.ajax({
                url : '/products/stockMode',
                data : { id: this.props.variation.id, stockMode: stockMode.value },
                method : 'POST',
                dataType : 'json',
                success : function(response) {
                    console.log(response);
                },
                error : function(response) {
                    console.log(response);
                }
            });
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
        updateStockLevel: function(name, value) {
            if (this.props.variation === null) {
                return;
            }
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: 'products/stockLevel',
                    type: 'POST',
                    dataType : 'json',
                    data: {
                        id: this.props.variation.id,
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
            return <tr>{this.getColumns(this.props.variation)}</tr>;
        }
    });

    return StockRowComponent;
});