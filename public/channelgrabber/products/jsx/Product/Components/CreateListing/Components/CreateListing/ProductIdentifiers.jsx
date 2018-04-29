define([
    'react',
    'Common/Components/EditableField',
    'Product/Components/CreateListing/Form/Shared/ImageDropDown'
], function(
    React,
    EditableField,
    ImageDropDown
) {
    "use strict";

    var ProductIdentifiers = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                currency: '£',
                images: true,
                attributeNames: [],
                editableAttributeNames: false,
                attributeNameMap: {},
                customFields: {},
                listingType: null,
                fetchVariations: function() {}
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
                if (this.props.editableAttributeNames) {
                    return <th><EditableField initialFieldText={attributeNameText} onSubmit={(fieldValue) => {
                        var attributeNameMap = Object.assign({}, this.props.attributeNameMap);
                        attributeNameMap[attributeName] = fieldValue;

                        this.props.setFormStateListing({attributeNameMap: attributeNameMap})

                        return new Promise(function(resolve, reject) {
                            resolve({ newFieldText: fieldValue });
                        });
                    }} /></th>
                }

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
        render: function() {
            console.log(this.props);
            return (
                <div className={"variation-picker"}>
                    <table>
                        <thead>
                        <tr>
                            {this.renderImageHeader()}
                            <th>SKU</th>
                            {this.renderAttributeHeaders()}
                        </tr>
                        </thead>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return ProductIdentifiers;
});
