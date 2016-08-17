define([
    'react'
], function(
    React
) {
    "use strict";

    var VariationViewComponent = React.createClass({
        getAttributeHeaders: function() {
            var headers = [
                <th key="image">Image</th>,
                <th key="sky">SKU</th>
            ];
            this.props.attributeNames.forEach(function(attributeName) {
                headers.push(<th key={attributeName}>{attributeName}</th>);
            });
            return headers;
        },
        getAttributeValues: function(variation) {
            var values = [
                <td key="image"><img src={variation.images.length > 0 ? variation.images[0]['url'] : this.context.imageBasePath + '/noproductsimage.png'} /></td>,
                <td key="sku">{variation.sku}</td>
            ];
            this.props.attributeNames.forEach(function (attributeName) {
                values.push(<td key={attributeName}>{variation.attributeValues[attributeName]}</td>);
            });
            return values;
        },
        getDefaultProps: function() {
            return {
                variations: []
            };
        },
        render: function () {
            return (
                <div className="variation-table">
                    <table>
                        <thead>
                            <tr>
                                {this.getAttributeHeaders()}
                            </tr>
                        </thead>
                        <tbody>
                            {this.props.variations.map(function (variation) {
                                return <tr key={variation.id}>{this.getAttributeValues(variation)}</tr>;
                            }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    VariationViewComponent.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return VariationViewComponent;
});