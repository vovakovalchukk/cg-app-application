define([
    'react'
], function(
    React
) {
    "use strict";

    var VariationPicker = React.createClass({
        getDefaultProps: function() {
            return {
                variations: []
            }
        },
        renderVariationRows: function () {
            return this.props.variations.map(function(variation) {
                return <tr>
                    <td>checkbox</td>
                    <td>{variation.sku}</td>
                    <td>{variation.price}</td>
                </tr>
            });
        },
        render: function() {
            return (
                <div>
                    <table>
                        <tr>
                            <td>checkbox</td>
                            <td>sku</td>
                            <td>price</td>
                        </tr>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return VariationPicker;
});
