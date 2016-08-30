define([
    'react'
], function(
    React
) {
    "use strict";

    var VariationViewComponent = React.createClass({
        getAttributeHeaders: function() {
            var headers = [];
            this.props.attributeNames.forEach(function(attributeName) {
                headers.push(<th key={attributeName}>{attributeName}</th>);
            });
            if (! headers.length) {
                headers.push(<th></th>);
            }
            return headers;
        },
        getAttributeValues: function(variation) {
            var values = [];
            this.props.attributeNames.forEach(function (attributeName) {
                values.push(<td key={attributeName}>{variation.attributeValues[attributeName]}</td>);
            });
            if (! values.length) {
                values.push(<td></td>);
            }
            return values;
        },
        getDefaultProps: function() {
            return {
                variations: [],
                attributeNames: [],
                fullView: false
            };
        },
        render: function () {
            var count = 0;
            return (
                <div className="variation-table">
                    <div className="image-sku-table">
                        <table>
                            <thead>
                                <tr>
                                    <th key="image" className="image-col">Image</th>
                                    <th key="sky" className="sku-col">SKU</th>
                                </tr>
                            </thead>
                            <tbody>
                                {this.props.variations.map(function (variation) {
                                    if ((! this.props.fullView) && count > 1) {
                                        return;
                                    }
                                    return (
                                        <tr key={variation.id}>
                                            <td key="image"><img src={variation.images.length > 0 ? variation.images[0]['url'] : this.context.imageBasePath + '/noproductsimage.png'} /></td>
                                            <td key="sku">{variation.sku}</td>
                                        </tr>
                                    );
                                }.bind(this))}
                            </tbody>
                        </table>
                    </div>
                    <div className="variations-table">
                        <table>
                            <thead>
                                <tr>
                                    {this.getAttributeHeaders()}
                                </tr>
                            </thead>
                            <tbody>
                                {this.props.variations.map(function (variation) {
                                    if ((! this.props.fullView) && count > 1) {
                                        return;
                                    }
                                    count++;
                                    return <tr key={variation.id}>{this.getAttributeValues(variation)}</tr>;
                                }.bind(this))}
                            </tbody>
                        </table>
                    </div>
                </div>
            );
        }
    });

    VariationViewComponent.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return VariationViewComponent;
});