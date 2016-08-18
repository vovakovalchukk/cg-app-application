define([
    'react',
    'Product/Components/Input'
], function(
    React,
    Input
) {
    "use strict";

    var StockViewComponent = React.createClass({
        getHeaders: function() {
            return [
                <th key="stock-available">Available</th>,
                <th key="stock-undispatched">Undispatched</th>,
                <th key="stock-total">Total Stock</th>,
                <th key="stock-mode">Stock Mode</th>,
                <th key="stock-level">Stock Level</th>,
            ];
        },
        getValues: function(variation) {
            return [
                <td key="stock-available" className="product-stock-available">
                    <Input name='available' value={(this.getOnHandStock(variation) - Math.max(this.getAllocatedStock(variation), 0))}/>
                </td>,
                <td key="stock-undispatched" className="product-stock-allocated">
                    <Input name='undispatched' value={this.getOnHandStock(variation)}/>
                </td>,
                <td key="stock-total" className="product-stock-available">
                    <Input name='total' value={this.getOnHandStock(variation)}/>
                    <input type='hidden' value={variation.eTag} />
                    <input type='hidden' value={variation.stock ? variation.stock.locations[0].eTag : ''} />
                </td>,
                <td key="stock-mode" className="product-stock-mode">
                    Dropdown
                </td>,
                <td key="stock-level" className="product-stock-level">
                    <Input name='level' value={this.getOnHandStock(variation)}/>
                </td>
            ];
        },
        getOnHandStock: function(variation) {
            return (variation.stock ? variation.stock.locations[0].onHand : '');
        },
        getAllocatedStock: function(variation) {
            return (variation.stock ? variation.stock.locations[0].allocated : '');
        },
        getDefaultProps: function() {
            return {
                variations: []
            };
        },
        render: function () {
            return (
                <div className="stock-table">
                    <table>
                        <thead>
                        <tr>
                            {this.getHeaders()}
                        </tr>
                        </thead>
                        <tbody>
                        {this.props.variations.map(function (variation) {
                            return <tr key={variation.id}>{this.getValues(variation)}</tr>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return StockViewComponent;
});