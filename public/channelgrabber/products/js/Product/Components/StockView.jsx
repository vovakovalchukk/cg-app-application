define([
    'react',
    'Product/Components/StockRow'
], function(
    React,
    StockRow
) {
    "use strict";

    var StockViewComponent = React.createClass({
        getHeaders: function() {
            return [
                <th key="stock-available">Available</th>,
                <th key="stock-undispatched">Undispatched</th>,
                <th key="stock-total">Total</th>,
                <th key="stock-mode">Mode</th>,
                <th key="stock-level">Fix Level</th>,
            ];
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
                            return <StockRow key={variation.id} variation={variation} updateUrl="products/stock/update"/>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return StockViewComponent;
});