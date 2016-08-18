define([
    'react',
    'Product/Components/Input'
], function(
    React,
    Input
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
                    <Input name='total' value={this.getOnHandStock(variation)} submitCallback={this.update}/>
                    <input type='hidden' value={variation.eTag} />
                    <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
                </td>,
                <td key="stock-mode" className="product-stock-mode">
                    Dropdown
                </td>,
                <td key="stock-level" className="product-stock-level">
                    <Input name='level' value={this.getOnHandStock(variation)} submitCallback={this.update}/>
                </td>
            ];
        },
        getOnHandStock: function(variation) {
            return (variation.stock ? variation.stock.locations[0].onHand : '');
        },
        getAllocatedStock: function(variation) {
            return (variation.stock ? variation.stock.locations[0].allocated : '');
        },
        getStockEtag: function(variation) {
            return (variation.stock ? variation.stock.locations[0].eTag : '');
        },
        getStockLocationId: function(variation) {
            return (variation.stock ? variation.stock.locations[0].id : '');
        },
        update: function(detail, value) {
            if (this.props.variation === null) {
                return;
            }
            console.log('Submitting stock '+detail+' as '+value+' to '+this.props.updateUrl);
            return;
            $.ajax({
                url: this.props.updateUrl,
                type: 'POST',
                dataType : 'json',
                data: {
                    'stockLocationId': this.getStockLocationId(variation),
                    'totalQuantity': value,
                    'eTag': this.getStockEtag(variation)
                },
                success: function() {
                    return true;
                },
                error: function() {
                    return false;
                }
            });
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