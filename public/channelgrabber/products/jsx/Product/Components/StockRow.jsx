define([
    'React',
    'Common/Components/SafeInput',
    'Common/Components/Select'
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
                    <div>{this.getAllocatedStock()}</div>
                </td>,
                <td key="stock-total" className="product-stock-available">
                    <Input name='total' initialValue={this.getOnHandStock()} submitCallback={this.updateStockTotal}/>
                    <input type='hidden' value={variation.eTag} />
                    <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
                </td>,
                <td key="stock-mode" colSpan="2" className="product-stock-mode">
                    <Select options={this.getStockModeOptions()} onOptionChange={this.updateStockMode} selectedOption={this.getSelectedOption()}/>
                </td>,
                <td key="stock-level" colSpan="1" className="product-stock-level">
                    <Input name='level' initialValue={this.getStockModeLevel()} submitCallback={this.updateStockLevel} disabled={this.shouldInputBeDisabled()} />
                </td>
            ];
        },
        getSelectedOption: function() {
            if (!this.props.variation.stock) {
                return;
            }
            return {
                name: this.props.variation.stockModeDesc ,
                value: this.props.variation.stock.stockMode
            }
        },
        shouldInputBeDisabled: function() {
            var disabledStockMode = 'all';
            var shouldBeDisabled = (!this.props.variation.stock) ||
                (   (this.props.variation.stock.stockMode === null || this.props.variation.stock.stockMode === 'null') &&
                (this.props.variation.stockModeDefault === null || this.props.variation.stockModeDefault === disabledStockMode) ) ||
                (this.props.variation.stock.stockMode === disabledStockMode);
            return shouldBeDisabled;
        },
        getStockModeLevel: function() {
            return this.props.variation.stock ? this.props.variation.stock.stockLevel : '';
        },
        getStockModeOptions: function() {
            if (!this.props.variation.stockModeOptions) {
                return [];
            }
            var options = [];
            this.props.variation.stockModeOptions.map(function(option) {
                options.push({value: option.value, name: option.title, selected: option.selected});
            });
            return options;
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
            n.notice('Updating stock mode.');
            $.ajax({
                url : '/products/stockMode',
                data : { id: this.props.variation.id, stockMode: stockMode.value },
                method : 'POST',
                dataType : 'json',
                success : function(response) {
                    n.success('Stock mode updated successfully..');
                    var modeUpdatedEvent = new CustomEvent('mode-'+this.props.variation.sku, {'detail': response});
                    window.dispatchEvent(modeUpdatedEvent);
                }.bind(this),
                error : function(error) {
                    n.showErrorNotification(error, "There was an error when attempting to update the stock mode.");
                }
            });
        },
        updateStockTotal: function(name, value) {
            if (this.props.variation === null) {
                return;
            }
            n.notice('Updating stock total.');
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
                        n.success('Stock total updated successfully..');
                        var totalUpdatedEvent = new CustomEvent('total-'+this.props.variation.sku, {'detail': {'value': value}});
                        window.dispatchEvent(totalUpdatedEvent);
                        resolve({ savedValue: value });
                    }.bind(this),
                    error: function(error) {
                        n.showErrorNotification(error, "There was an error when attempting to update the stock total.");
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        updateStockLevel: function(name, value) {
            if (this.props.variation === null) {
                return;
            }
            n.notice('Updating stock level.');
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: 'products/stockLevel',
                    type: 'POST',
                    dataType : 'json',
                    data: {
                        id: this.props.variation.id,
                        stockLevel: value
                    },
                    success: function(response) {
                        n.success('Stock level updated successfully..');
                        var modeUpdatedEvent = new CustomEvent('mode-'+this.props.variation.sku, {'detail': response});
                        window.dispatchEvent(modeUpdatedEvent);
                        resolve({ savedValue: response[this.props.variation.sku].level || 0 });
                    }.bind(this),
                    error: function(error) {
                        n.showErrorNotification(error, "There was an error when attempting to update the stock level.");
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
        componentDidMount: function () {
            window.addEventListener('total-'+this.props.variation.sku, this.props.totalUpdated);
            window.addEventListener('mode-'+this.props.variation.sku, this.props.modeUpdated);
        },
        componentWillUnmount: function () {
            window.removeEventListener('total-'+this.props.variation.sku, this.props.totalUpdated);
            window.removeEventListener('mode-'+this.props.variation.sku, this.props.modeUpdated);
        },
        render: function () {
            return <tr>{this.getColumns(this.props.variation)}</tr>;
        }
    });

    return StockRowComponent;
});
