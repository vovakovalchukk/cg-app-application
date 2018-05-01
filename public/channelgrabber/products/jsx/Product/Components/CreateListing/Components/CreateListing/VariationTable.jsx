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
                images: true,
                attributeNames: [],
                attributeNameMap: {},
                sectionName: '',
                renderCustomTableHeaders: function() {return null},
                renderCustomTableRows: function() {return null}
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
            if (!this.props.images) {
                return;
            }
            if (this.props.product.images == 0) {
                return <td>No images available</td>
            }

            return (<td>
                <Field
                    name={variation.sku + ".imageId"}
                    component={this.renderImageField}
                    variation={variation}
                />
            </td>);
        },
        renderImageField: function(field) {
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
                <FormSection name={this.props.sectionName}>
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
                </FormSection>
            );
        }
    });

    return VariationTableComponent;
});
