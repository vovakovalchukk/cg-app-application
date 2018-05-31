define([
    'react',
    'Product/Components/Checkbox',
    'Common/Components/CurrencyInput',
    'Common/Components/EditableFieldWithSubmit',
    'Product/Components/CreateListing/Form/Shared/ImageDropDown'
], function(
    React,
    Checkbox,
    CurrencyInput,
    EditableFieldWithSubmit,
    ImageDropDown
) {
    "use strict";

    var VariationPicker = React.createClass({

        getDefaultProps: function() {
            /*
             * @customFields - object -
             * {
             *     String <key>: {
             *           displayName: String <displayName>,
             *           getFormComponent: Fnc (value, onChange) return: ReactComponent <componentForCustomField>,
             *           * note - onChange is a callback which must be used as the onChange for the component which you return
             *           getDefaultValueFromVariation: Fnc(variation) return String <valueOfCustomFieldGivenAVariation>
             *       }
             * }
             *
             */
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
        getInitialState: function() {
            return {
                variationsFormState: {},
                allChecked: true
            }
        },
        componentDidMount: function() {
            if (this.props.product.variationCount > this.props.variationsDataForProduct.length) {
                this.props.fetchVariations({detail: {productId: this.props.product.id}}, false);
            }
            this.createVariationFormStateFromProps(this.props);
        },
        componentWillReceiveProps(newProps) {
            if (newProps.variationsDataForProduct != this.props.variationsDataForProduct) {
                this.createVariationFormStateFromProps(newProps);
            }
        },
        createVariationFormStateFromProps: function(newProps) {
            var variationsFormState = {};
            for (var variationIndex in newProps.variationsDataForProduct) {
                var currentVariation = newProps.variationsDataForProduct[variationIndex];
                variationsFormState[currentVariation.id] = {
                    checked: true,
                    price: currentVariation.details ? currentVariation.details.price : null
                }

                if (this.props.images) {
                    var image = (currentVariation.images.length > 0 ? currentVariation.images[0] : this.props.product.images[0]);
                    variationsFormState[currentVariation.id]['imageId'] = image.id;
                }

                for (var fieldName in this.props.customFields) {
                    variationsFormState[currentVariation.id][fieldName] = this.props.customFields[fieldName].getDefaultValueFromVariation(currentVariation);
                }
            }

            this.setState({
                variationsFormState: variationsFormState
            });
        },
        componentDidUpdate: function(prevProps, prevState) {
            var productId = this.props.product.id;
            var listingFormVariationState = this.getListingFormVariationState();
            var listingType = 'variation';

            if (this.isSingleListing(listingFormVariationState)) {
                var selectedVariationId = Object.keys(listingFormVariationState)[0];
                productId = parseInt(selectedVariationId);
                listingFormVariationState = null;
                listingType = 'single';
            }

            this.props.setFormStateListing({
                variations: listingFormVariationState,
                productId: productId,
                listingType: listingType
            });
        },
        getListingFormVariationState: function()
        {
            var listingFormVariationState = {};
            for (var variationId in this.state.variationsFormState) {
                var currentVariation = Object.assign({}, this.state.variationsFormState[variationId]);

                if (currentVariation.checked == false) {
                    continue;
                }

                delete (currentVariation.checked);

                listingFormVariationState[variationId] = currentVariation;
            }
            return listingFormVariationState;
        },
        isSingleListing: function(listingFormVariationState) {
            return (this.props.listingType == 'single' && Object.keys(listingFormVariationState).length == 1);
        },
        onCheckBoxClick: function(variationId) {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);
            variationsFormState[variationId].checked = ! variationsFormState[variationId].checked;

            var allChecked = true;
            for (var variationId in variationsFormState) {
                if (variationsFormState[variationId].checked == false) {
                    allChecked = false;
                    break;
                }
            }

            this.setState({
                variationsFormState: variationsFormState,
                allChecked: allChecked
            })
        },
        onCheckAll: function() {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);

            var newCheckedState = ! this.state.allChecked;
            for (var variationId in variationsFormState) {
                variationsFormState[variationId].checked = newCheckedState;
            }

            this.setState({
                variationsFormState: variationsFormState,
                allChecked: ! this.state.allChecked
            })
        },
        shouldComponentUpdate(nextProps, nextState) {
            return nextState.variationsFormState != this.state.variationsFormState
                || this.props.variationsDataForProduct != nextProps.variationsDataForProduct
                || nextProps.listingType != this.props.listingType;
        },
        onVariationValueChange: function(variationId, fieldName, event) {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);
            if (!variationsFormState[variationId]) {
                variationsFormState[variationId] = {};
            }
            variationsFormState[variationId][fieldName] = event.target.value;

            this.setState({
                variationsFormState: variationsFormState
            })
        },
        renderAttributeHeaders: function () {
            return this.props.attributeNames.map(function(attributeName) {
                var attributeNameText = this.props.attributeNameMap[attributeName] ? this.props.attributeNameMap[attributeName] : attributeName;
                if (this.props.editableAttributeNames) {
                    return <th><EditableFieldWithSubmit initialFieldText={attributeNameText} onSubmit={(fieldValue) => {
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
        renderImageHeader: function() {
            if (!this.props.images) {
                return;
            }
            return <th>Image</th>;
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
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
                    onChange={this.onVariationValueChange.bind(this, variation.id, 'imageId')}
                />
            </td>;
        },
        renderVariationRows: function () {
            return this.props.variationsDataForProduct.map(function(variation) {
                return <tr>
                    <td>
                        <Checkbox
                            id={variation.id}
                            onClick={this.onCheckBoxClick.bind(this, variation.id)}
                            isChecked={this.state.variationsFormState[variation.id] ? this.state.variationsFormState[variation.id].checked : null}
                        />
                    </td>
                    <td>{variation.sku}</td>
                    {this.renderAttributeColumns(variation)}
                    {this.renderImageColumn(variation)}
                    <td>
                        <CurrencyInput
                            value={this.state.variationsFormState[variation.id]? this.state.variationsFormState[variation.id].price: null}
                            onChange={this.onVariationValueChange.bind(this, variation.id, 'price')}
                            currency={this.props.currency}
                        />
                    </td>
                    {this.renderCustomFields(variation.id)}
                </tr>
            }.bind(this));
        },
        renderChannelSpecificFieldHeaders: function() {
            for (var fieldName in this.props.customFields) {
                var fieldDisplayName = this.props.customFields[fieldName]['displayName']
                    ? this.props.customFields[fieldName]['displayName']
                    : fieldName;
                return <th>
                    {fieldDisplayName}
                </th>
            }
        },
        renderCustomFields: function(variationId) {
            for (var fieldName in this.props.customFields) {
                var value = this.state.variationsFormState[variationId] && this.state.variationsFormState[variationId][fieldName]
                    ? this.state.variationsFormState[variationId][fieldName]
                    : null;

                var elementForChannelSpecificField = this.props.customFields[fieldName].getFormComponent(
                    value,
                    this.onVariationValueChange.bind(this, variationId, fieldName)
                );
                return <td>
                    {elementForChannelSpecificField}
                </td>
            }
        },
        render: function() {
            return (
                <div className={"variation-picker"}>
                    <table>
                        <thead>
                            <tr>
                                <th className={'variation-picker-checkbox'}>
                                    <Checkbox onClick={this.onCheckAll} isChecked={this.state.allChecked} />
                                </th>
                                <th>SKU</th>
                                {this.renderAttributeHeaders()}
                                {this.renderImageHeader()}
                                <th>Price</th>
                                {this.renderChannelSpecificFieldHeaders()}
                            </tr>
                        </thead>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return VariationPicker;
});
