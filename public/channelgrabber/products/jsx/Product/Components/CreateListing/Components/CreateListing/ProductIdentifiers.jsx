define([
    'react',
    'redux-form',
    'Product/Components/CreateListing/Form/Shared/ImageDropDown'
], function(
    React,
    ReduxForm,
    ImageDropDown
) {
    "use strict";

    var FormSection = ReduxForm.FormSection;
    var Field = ReduxForm.Field;

    var identifiers = [
        {"name": "ean", "displayTitle" : "EAN (European barcode)"},
        {"name": "upc", "displayTitle" : "UPC (Widely used in NA)"},
        {"name": "mpn", "displayTitle" : "MPN (if applicable)"},
        {"name": "isbn", "displayTitle" : "ISBN (if applicable)"}
    ];

    var ProductIdentifiers = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                images: true,
                attributeNames: [],
                attributeNameMap: {},
            }
        },
        renderImageHeader: function() {
            if (!this.props.images) {
                return;
            }
            return <th>Image</th>;
        },
        renderAttributeHeaders: function () {
            return this.props.attributeNames.map(function(attributeName) {
                var attributeNameText = this.props.attributeNameMap[attributeName] ? this.props.attributeNameMap[attributeName] : attributeName;
                return <th>
                    {attributeNameText}
                </th>;
            }.bind(this));
        },
        renderIdentifierReaders: function () {
            return identifiers.map(function (identifier) {
                return <th>{identifier.displayTitle}</th>;
            });
        },
        renderVariationRows: function () {
            return this.props.variationsDataForProduct.map(function(variation) {
                return <tr>
                    {this.renderImageColumn(variation)}
                    <td>{variation.sku}</td>
                    {this.renderAttributeColumns(variation)}
                    {this.renderIdentifierColumns(variation)}
                </tr>
            }.bind(this));
        },
        renderImageColumn: function(variation) {
            if (!this.props.images) {
                return;
            }
            if (this.props.product.images == 0) {
                return <td>No images available</td>
            }
            var selected = (variation.images.length > 0 ? variation.images[0] : this.props.product.images[0]);
            return <td>
                <ImageDropDown
                    selected={selected}
                    autoSelectFirst={false}
                    images={this.props.product.images}
                    onChange={function() {console.log(arguments);}}
                />
            </td>;
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
        },
        renderIdentifierColumns: function (variation) {
            return identifiers.map(function (identifier) {
                return (<td>
                    <Field name={variation.sku + "." + identifier.name} component={"input"} type="text"/>
                </td>)
            });
        },
        render: function() {
            return (
                <FormSection name="identifiers">
                    <div className={"variation-picker"}>
                        <table>
                            <thead>
                            <tr>
                                {this.renderImageHeader()}
                                <th>SKU</th>
                                {this.renderAttributeHeaders()}
                                {this.renderIdentifierReaders()}
                            </tr>
                            </thead>
                            {this.renderVariationRows()}
                        </table>
                    </div>
                </FormSection>
            );
        }
    });

    return ProductIdentifiers;
});
