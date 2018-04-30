define([
    'react',
    'redux-form',
    'Common/Components/Input'
], function(
    React,
    ReduxForm,
    Input
) {
    "use strict";

    var Field = ReduxForm.Field;

    var validateNumber = function (value) {
        if (isNaN(Number(value))) {
            return 'Must be a number';
        }
        return undefined;
    };

    var ProductPriceComponent = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                images: true,
                attributeNames: [],
                attributeNameMap: {},
                change: function () {},
                accounts: {},
                initialProductPrices: {}
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
        renderPriceHeaders: function () {
            return this.props.accounts.map(function (account) {
                return <th>{account.displayName}</th>;
            });
        },
        renderVariationRows: function () {
            return this.props.variationsDataForProduct.map(function(variation) {
                return <tr>
                    {this.renderImageColumn(variation)}
                    <td>{variation.sku}</td>
                    {this.renderAttributeColumns(variation)}
                    {this.renderPriceColumns(variation)}
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
                    name={"identifiers." + variation.sku + ".imageId"}
                    component={this.renderImageField}
                />
            </td>);
        },
        renderImageField: function(field) {
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
            this.props.product.images.map(function(image) {
                if (image.id == imageId) {
                    selectedImage = image;
                }
            });
            return selectedImage;
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
        },
        renderPriceColumns: function (variation) {
            return this.props.accounts.map(function (account) {
                return (<td>
                    <Field
                        name={"prices." + variation.sku + "." + account.id}
                        component={this.renderInputComponent.bind(this, account.id, variation.sku)}
                        validate={[validateNumber]}
                    />
                </td>)
            }.bind(this));
        },
        renderInputComponent: function(accountId, sku, field) {
            var errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];
            return <Input
                {...field.input}
                name={field.input.name}
                value={field.input.value}
                onChange={this.onInputChange.bind(this, field.input, accountId, sku)}
                errors={errors}
            />;
        },
        onInputChange: function(input, accountId, sku, value) {
            input.onChange(value.target.value);
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
                            {this.renderPriceHeaders()}
                        </tr>
                        </thead>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return ProductPriceComponent;
});
