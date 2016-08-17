define([
    'react'
], function(
    React
) {
    "use strict";

    var StockViewComponent = React.createClass({
        getHeaders: function() {
            return [
                <th key="available">Available</th>,
                <th key="undispatched">Undispatched</th>,
                <th key="total-stock">Total Stock</th>,
                <th key="fix-level">Fix the level at</th>,
            ];
        },
        getValues: function(details) {
            return [
                <td key="available"></td>,
                <td key="undispatched"></td>,
                <td key="total-stock"></td>,
                <td key="fix-level"></td>,
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
                            return <tr key={variation.id}>{this.getValues()}</tr>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return StockViewComponent;
});