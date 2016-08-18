define([
    'react',
    'Product/Components/Input'
], function(
    React,
    Input
) {
    "use strict";

    var DimensionsViewComponent = React.createClass({
        getHeaders: function() {
            return [
                <th key="weight">Weight (kg)</th>,
                <th key="height">Height (cm)</th>,
                <th key="width">Width (cm)</th>,
                <th key="length">Length (cm)</th>,
            ];
        },
        getValues: function(variation) {
            return [
                <td key="weight" class="detail" data-id={variation.id} data-sku={variation.sku}>
                    <Input name='weight' value={variation.details ?variation.details.weight: ''} type="number"/>
                </td>,
                <td key="height" class="detail" data-id={variation.id} data-sku={variation.sku}>
                    <Input name='height' value={variation.details ?variation.details.height: ''} type="number"/>
                </td>,
                <td key="width" class="detail" data-id={variation.id} data-sku={variation.sku}>
                    <Input name='width' value={variation.details ?variation.details.width: ''} type="number"/>
                </td>,
                <td key="length" class="detail" data-id={variation.id} data-sku={variation.sku}>
                    <Input name='length' value={variation.details ?variation.details.length: ''} type="number"/>
                </td>,
            ];
        },
        getDefaultProps: function() {
            return {
                variations: []
            };
        },
        render: function () {
            return (
                <div className="details-table">
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

    return DimensionsViewComponent;
});