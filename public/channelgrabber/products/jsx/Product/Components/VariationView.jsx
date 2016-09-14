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
                var sortData = this.props.variationsSort.find(function (sort) {
                    return sort.attribute === attributeName;
                });
                headers.push(
                    <th className='sortable' key={attributeName} onClick={this.props.onColumnSortClick.bind(this, attributeName)}>
                        {attributeName}{sortData ? <span className="sort-dir">{sortData.ascending ? '▼' : '▲'}</span> : ''}
                    </th>
                );
            }.bind(this));
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
        getImageUrl: function(variation) {
            if (variation.images.length > 0) {
                return variation.images[0]['url'];
            }

            if (this.props.parentProduct.images && this.props.parentProduct.images.length > 0) {
                return this.props.parentProduct.images[0]['url'];
            }
            return this.context.imageBasePath + '/noproductsimage.png';
        },
        getDefaultProps: function() {
            return {
                variations: [],
                attributeNames: [],
                parentProduct: {},
                fullView: false
            };
        },
        render: function () {
            var imageRow = 0;
            var variationRow = 0;
            return (
                <div className="variation-table">
                    <div className="image-sku-table">
                        <table>
                            <thead>
                                <tr>
                                    <th key="image" className="image-col"></th>
                                    <th key="sky" className="sku-col">SKU</th>
                                </tr>
                            </thead>
                            <tbody>
                                {this.props.variations.map(function (variation) {
                                    if ((! this.props.fullView) && imageRow > 1) {
                                        return;
                                    }
                                    imageRow++;
                                    return (
                                        <tr key={variation.id}>
                                            <td key="image"><img src={this.getImageUrl(variation)} /></td>
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
                                    if ((! this.props.fullView) && variationRow > 1) {
                                        return;
                                    }
                                    variationRow++;
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