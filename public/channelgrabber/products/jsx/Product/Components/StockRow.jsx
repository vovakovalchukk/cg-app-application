import React from 'react';
import Input from 'Common/Components/SafeInput';
import Select from 'Common/Components/Select';


class StockRowComponent extends React.Component {
    static defaultProps = {
        variation: null,
        isFetchingStock: false
    };

    state = {
        stockMode: {
            name: '',
            value: ''
        }
    };

    getStockAvailable = () => {
        if (this.props.isFetchingStock) {
            return '';
        }
        return this.getOnHandStock() - Math.max(this.getAllocatedStock(), 0);
    };

    getColumns = (variation) => {
        return [
            <td key="stock-available" className="product-stock-available">
                <div>{this.getStockAvailable()}</div>
            </td>,
            <td key="stock-undispatched" className="product-stock-allocated">
                <div>{this.getAllocatedStock()}</div>
            </td>,
            <td key="stock-total" className="product-stock-available">
                <Input
                    name='total'
                    initialValue={this.getOnHandStock()}
                    submitCallback={this.updateStockTotal}
                    disabled={this.props.isFetchingStock}
                />
                <input type='hidden' value={variation.eTag} />
                <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
            </td>,
            <td key="stock-mode" colSpan="2" className="product-stock-mode">
                <Select
                    options={this.getStockModeOptions()}
                    onOptionChange={this.updateStockMode}
                    selectedOption={this.getSelectedOption()}
                    disabled={this.props.isFetchingStock}
                />
            </td>,
            <td key="stock-level" colSpan="1" className="product-stock-level">
                <Input
                    name='level'
                    initialValue={this.getStockModeLevel()}
                    submitCallback={this.updateStockLevel}
                    disabled={this.shouldInputBeDisabled()}
                />
            </td>
        ];
    };

    getSelectedOption = () => {
        if (!this.props.variation.stock) {
            return;
        }
        return {
            name: this.props.variation.stockModeDesc ,
            value: this.props.variation.stock.stockMode
        }
    };

    shouldInputBeDisabled = () => {
        if (this.props.isFetchingStock) {
            return true;
        }
        var disabledStockMode = 'all';
        var shouldBeDisabled = (!this.props.variation.stock) ||
            (   (this.props.variation.stock.stockMode === null || this.props.variation.stock.stockMode === 'null') &&
            (this.props.variation.stockModeDefault === null || this.props.variation.stockModeDefault === disabledStockMode) ) ||
            (this.props.variation.stock.stockMode === disabledStockMode);
        return shouldBeDisabled;
    };

    getStockModeLevel = () => {
        return this.props.variation.stock ? this.props.variation.stock.stockLevel : '';
    };

    getStockModeOptions = () => {
        if (!this.props.variation.stockModeOptions) {
            return [];
        }
        var options = [];
        this.props.variation.stockModeOptions.map(function(option) {
            options.push({value: option.value, name: option.title, selected: option.selected});
        });
        return options;
    };

    getOnHandStock = () => {
        return (this.props.variation.stock ? this.props.variation.stock.locations[0].onHand : '');
    };

    getAllocatedStock = () => {
        if (this.props.isFetchingStock) {
            return '';
        }
        return (this.props.variation.stock ? this.props.variation.stock.locations[0].allocated : '');
    };

    getStockEtag = () => {
        return (this.props.variation.stock ? this.props.variation.stock.locations[0].eTag : '');
    };

    getStockLocationId = () => {
        return (this.props.variation.stock ? this.props.variation.stock.locations[0].id : '');
    };

    updateStockMode = (stockMode) => {
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
                window.triggerEvent('mode-'+this.props.variation.sku, response);
            }.bind(this),
            error : function(error) {
                n.showErrorNotification(error, "There was an error when attempting to update the stock mode.");
            }
        });
    };

    updateStockTotal = (name, value) => {
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
                    window.triggerEvent('total-'+this.props.variation.sku, {'value': value});
                    resolve({ savedValue: value });
                }.bind(this),
                error: function(error) {
                    n.showErrorNotification(error, "There was an error when attempting to update the stock total.");
                    reject(new Error(error));
                }
            });
        }.bind(this));
    };

    updateStockLevel = (name, value) => {
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
                    window.triggerEvent('mode-'+this.props.variation.sku, response);
                    resolve({ savedValue: response[this.props.variation.sku].level || 0 });
                }.bind(this),
                error: function(error) {
                    n.showErrorNotification(error, "There was an error when attempting to update the stock level.");
                    reject(new Error(error));
                }
            });
        }.bind(this));
    };

    componentDidMount() {
        window.addEventListener('total-'+this.props.variation.sku, this.props.totalUpdated);
        window.addEventListener('mode-'+this.props.variation.sku, this.props.modeUpdated);
    }

    componentWillUnmount() {
        window.removeEventListener('total-'+this.props.variation.sku, this.props.totalUpdated);
        window.removeEventListener('mode-'+this.props.variation.sku, this.props.modeUpdated);
    }

    render() {
        return <tr>{this.getColumns(this.props.variation)}</tr>;
    }
}

export default StockRowComponent;

