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
                <th key="stock-available"><abbr title="Quantity of item available for sale">Available</abbr></th>,
                <th key="stock-undispatched"><abbr title="Quantity of item currently awaiting dispatch">Undispatched</abbr></th>,
                <th key="stock-total">Total</th>,
                <th key="stock-mode">Mode</th>,
                <th key="stock-level"><abbr title="Quantity of items that will be listed, according to the currently chosen Stock Mode.">Level</abbr></th>,
            ];
        },
        getDefaultProps: function() {
            return {
                variations: [],
                fullView: false
            };
        },
        render: function () {
            var count = 0;
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
                            if ((! this.props.fullView) && count > 1) {
                                return;
                            }
                            count++;
                            return <StockRow key={variation.id} variation={variation} />;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return StockViewComponent;
});