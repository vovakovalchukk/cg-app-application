define([
    'react'
], function(
    React
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
                <td key="weight" class="detail" data-id={variation.id} data-sku={variation.sku}><div class="detail-text-holder">{variation.details ?variation.details.weight: ''}</div></td>,
                <td key="height" class="detail" data-id={variation.id} data-sku={variation.sku}><div class="detail-text-holder">{variation.details ?variation.details.height: ''}</div></td>,
                <td key="width" class="detail" data-id={variation.id} data-sku={variation.sku}><div class="detail-text-holder">{variation.details ?variation.details.width: ''}</div></td>,
                <td key="length" class="detail" data-id={variation.id} data-sku={variation.sku}><div class="detail-text-holder">{variation.details ?variation.details.length: ''}</div></td>,
            ];
        },
        getDefaultProps: function() {
            return {
                variations: []
            };
        },
        render: function () {
            //console.log(this.props.variations);
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