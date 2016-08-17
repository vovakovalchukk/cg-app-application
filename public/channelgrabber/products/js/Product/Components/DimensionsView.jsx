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
        getValues: function(details) {
            return [
                <td key="weight">{details.weight}</td>,
                <td key="height">{details.height}</td>,
                <td key="width">{details.width}</td>,
                <td key="length">{details.length}</td>,
            ];
        },
        getDefaultProps: function() {
            return {
                variations: []
            };
        },
        render: function () {
            console.log(this.props.variations);
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
                            return <tr key={variation.id}>{this.getValues(variation.details)}</tr>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return DimensionsViewComponent;
});