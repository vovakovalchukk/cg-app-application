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
                initialPrices: {},
                touchedPrices: {}
            }
        },
        componentWillReceiveProps: function(newProps) {
            if (Object.keys(this.props.initialPrices).length > 0) {
                return;
            }

            this.setTouchedPricesFromInitialPrices(newProps);
        },
        setTouchedPricesFromInitialPrices: function(props) {
            var touchedPrices = {};

            props.accounts.map(function(account) {
                var touchedPricesForAccount = {};
                props.variationsDataForProduct.map(function(variation) {
                    var isTouched = false;
                    if (props.initialPrices[variation.sku] && props.initialPrices[variation.sku][account.id]) {
                        isTouched = true;
                    }
                    touchedPricesForAccount[variation.sku] = isTouched;
                });
                touchedPrices[account.id] = touchedPricesForAccount
            });

            this.setState({
                touchedPrices: touchedPrices
            });
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
            if (this.isFirstVariationRow(sku)) {
                this.props.variationsDataForProduct.map(function (variation) {
                    if (sku == variation.sku) {
                        return;
                    }
                    if (accountId in this.state.touchedPrices
                        && variation.sku in this.state.touchedPrices[accountId]
                        && this.state.touchedPrices[accountId][variation.sku]) {
                        return;
                    }
                    this.props.change("prices." + variation.sku + "." + accountId, value.target.value);
                }.bind(this));
            } else {
                this.markPriceAsTouchedForSkuAndAccount(sku, accountId);
            }
        },
        isFirstVariationRow: function(sku) {
            if (sku == this.props.variationsDataForProduct[0].sku) {
                return true;
            }
            return false;
        },
        markPriceAsTouchedForSkuAndAccount: function(sku, accountId) {
            if (this.state.touchedPrices[accountId][sku]) {
                return;
            }

            var touchedPrices = Object.assign({}, this.state.touchedPrices);
            touchedPrices[accountId][sku] = true;
            this.setState({
                touchedPrices: touchedPrices
            });
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
