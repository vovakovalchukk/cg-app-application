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
            return ["ean", "upc", "mpn", "isbn"].map(function (identifierName) {
                return (<td>
                    <Field name={variation.sku + "." + identifierName} component={"input"} type="text"/>
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
                                <th>EAN (European barcode)</th>
                                <th>UPC (Widely used in NA)</th>
                                <th>MPN (if applicable)</th>
                                <th>ISBN (if applicable)</th>
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
