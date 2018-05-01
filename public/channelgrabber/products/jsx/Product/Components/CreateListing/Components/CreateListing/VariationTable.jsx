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

    var VariationTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                showImages: true,
                renderImagePicker: true,
                attributeNames: [],
                attributeNameMap: {},
                sectionName: '',
                renderCustomTableHeaders: function() {return null},
                renderCustomTableRows: function() {return null}
            }
        },
        renderImageHeader: function() {
            if (!this.props.showImages) {
                return;
            }
            return <th>Image</th>;
        },
        renderAttributeHeaders: function () {
            return this.props.attributeNames.map(function(attributeName) {
                var attributeNameText = this.props.attributeNameMap[attributeName] ? this.props.attributeNameMap[attributeName] : attributeName;
                return <th
                    className="attribute-header with-title"
                    title={attributeNameText}
                >
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
                    {this.props.renderCustomTableRows(variation)}
                </tr>
            }.bind(this));
        },
        renderImageColumn: function(variation) {
            if (!this.props.showImages) {
                return;
            }
            if (this.props.product.images == 0) {
                return <td>No images available</td>
            }

            return (<td>
                <Field
                    name={"identifiers." + variation.sku + ".imageId"}
                    component={this.renderImageField}
                    variation={variation}
                />
            </td>);
        },
        renderImageField: function(field) {
            if (!this.props.renderImagePicker) {
                return this.renderStaticImage(field);
            }
            var selected = (field.variation.images.length > 0 ? field.variation.images[0] : this.props.product.images[0]);
            return <ImageDropDown
                selected={selected}
                autoSelectFirst={false}
                images={this.props.product.images}
                onChange={this.onImageSelected.bind(this, field)}
            />
        },
        onImageSelected: function(field, image) {
            this.onInputChange(field.input, image.target.value);
        },
        renderStaticImage: function(field) {
            var image = this.findSelectedImageForVariation(field.input.value);
            return (
                <div className="image-dropdown-target">
                    <div className="react-image-picker">
                        <span className="react-image-picker-image">
                            <img src={image.url}/>
                        </span>
                    </div>
                </div>

            );
        },
        findSelectedImageForVariation: function(imageId) {
            var selectedImage = {url: ""};
            if (!imageId) {
                return selectedImage;
            }
            var foundImage = this.props.product.images.find(image => image.id == imageId);
            return foundImage ? foundImage : selectedImage;
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
        },
        onInputChange: function(input, value) {
            input.onChange(value);
        },
        render: function() {
            return (
                <div className={"variation-picker"}>
                    <table>
                        <thead>
                        <tr>
                            {this.renderImageHeader()}
                            <th>SKU</th>
                            {this.renderAttributeHeaders()}
                            {this.props.renderCustomTableHeaders()}
                        </tr>
                        </thead>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return VariationTableComponent;
});
