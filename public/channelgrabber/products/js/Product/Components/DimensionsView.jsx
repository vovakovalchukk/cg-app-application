define([
    'react',
    'Product/Components/DimensionsRow'
], function(
    React,
    DimensionsRow
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
        getDefaultProps: function() {
            return {
                variations: [],
                fullView: false
            };
        },
        render: function () {
            var count = 0;
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
                            if (this.props.fullView && count > 1) {
                                return;
                            }
                            count++;
                            return <DimensionsRow key={variation.id} variation={variation}/>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return DimensionsViewComponent;
});