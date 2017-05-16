define([
    'react',
    'Product/Components/Image',
    'Product/Components/Link'
], function(
    React,
    Image,
    Link
) {
    "use strict";

    var VariationViewComponent = React.createClass({
        getDefaultProps: function() {
            return {
                variations: [],
                attributeNames: [],
                parentProduct: {},
                fullView: false
            };
        },
        getInitialState: function () {
            return {
                tableWidth: 0
            }
        },
        componentDidMount: function() {
            const width = document.getElementsByClassName('variations-table')[0].clientWidth;
            this.setState({ tableWidth: width });
        },
        getAttributeHeaders: function() {
            var columnWidth = this.state.tableWidth/this.props.maxVariationAttributes;

            var headers = [];
            this.props.attributeNames.forEach(function(attributeName) {
                var sortData = this.props.variationsSort.find(function (sort) {
                    return sort.attribute === attributeName;
                });
                headers.push(
                    <th style={{width: columnWidth}} className='sortable' key={attributeName} onClick={this.props.onColumnSortClick.bind(this, attributeName)}>
                        {attributeName}{sortData ? <span className="sort-dir">{sortData.ascending ? '▼' : '▲'}</span> : ''}
                    </th>
                );
            }.bind(this));
            while (headers.length < this.props.maxVariationAttributes) {
                headers.push(<th style={{width: columnWidth}}></th>);
            }
            return headers;
        },
        getAttributeValues: function(variation) {
            var values = [];
            this.props.attributeNames.forEach(function (attributeName) {
                values.push(<td key={attributeName} title={variation.attributeValues[attributeName]} className="variation-attribute-col ellipsis">{variation.attributeValues[attributeName]}</td>);
            });
            while (values.length < this.props.maxVariationAttributes) {
                values.push(<td className="variation-attribute-col"></td>);
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
        render: function () {
            var imageRow = 0;
            var variationRow = 0;
            var noVariations = this.props.variations.length == 1;
            return (
                <div className="variation-table">
                    <div className={"image-sku-table" + (noVariations ? ' full' : '')}>
                        <table>
                            <thead>
                                <tr>
                                    <th key="image" className="image-col"></th>
                                    <th key="sku" className="sku-col">SKU</th>
                                    <th key="link" className="link-col">Link</th>
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
                                            <td key="image" className="image-cell"><Image src={this.getImageUrl(variation)} /></td>
                                            <td is class="sku-cell ellipsis" data-copy={variation.sku} title={variation.sku + ' (Click to Copy)'}>{variation.sku}</td>
                                            <td key="link" className="link-cell"><Link linkedProducts={[]}/></td>
                                        </tr>
                                    );
                                }.bind(this))}
                            </tbody>
                        </table>
                    </div>
                    <div className={"variations-table" + (noVariations ? ' hide' : '')}>
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