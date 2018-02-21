define([
    'react',
    'Product/Components/Checkbox',
    'Common/Components/CurrencyInput',
    'Common/Components/EditableField'
], function(
    React,
    Checkbox,
    CurrencyInput,
    EditableField
) {
    "use strict";

    var VariationPicker = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                currency: '£',
                attributeNames: [],
                editableAttributeNames: false,
                attributeNameMap: {},
                channelSpecificFields: {},
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

                for (var fieldName in this.props.channelSpecificFields) {
                    variationsFormState[currentVariation.id][fieldName] = this.props.channelSpecificFields[fieldName].getDefaultValueFromVariation(currentVariation);
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

            var newCheckedState = this.state.allChecked ? false : true;
            for (var variationId in variationsFormState) {
                variationsFormState[variationId].checked = newCheckedState;
            }

            this.setState({
                variationsFormState: variationsFormState,
                allChecked: ! this.state.allChecked
            })
        },
        shouldComponentUpdate(nextProps, nextState) {
            if (nextState.variationsFormState != this.state.variationsFormState ||
                this.props.variationsDataForProduct != nextProps.variationsDataForProduct ||
                nextProps.listingType != this.props.listingType
            ) {
                return true;
            }
            return false;
        },
        onVariationValueChange: function(variationId, fieldName, event) {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);
            variationsFormState[variationId][fieldName] = event.target.value;

            this.setState({
                variationsFormState: variationsFormState
            })
        },
        renderAttributeHeaders: function () {
            return this.props.attributeNames.map(function(attributeName) {
                if (this.props.editableAttributeNames) {
                    return <td><EditableField initialFieldText={attributeName} onSubmit={(fieldValue) => {
                        var attributeNameMap = Object.assign({}, this.props.attributeNameMap);
                        attributeNameMap[attributeName] = fieldValue;

                        this.props.setFormStateListing({attributeNameMap: attributeNameMap})

                        return new Promise(function(resolve, reject) {
                            resolve({ newFieldText: fieldValue });
                        });
                    }} /></td>
                }

                return <td>
                    {this.props.attributeNameMap[attributeName] ? this.props.attributeNameMap[attributeName] : attributeName}
                </td>;
            }.bind(this));
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
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
                    <td>
                        <CurrencyInput
                            value={this.state.variationsFormState[variation.id]? this.state.variationsFormState[variation.id].price: null}
                            onChange={this.onVariationValueChange.bind(this, variation.id, 'price')}
                            currency={this.props.currency}
                        />
                    </td>
                    {this.renderChannelSpecificFields(variation.id)}
                </tr>
            }.bind(this));
        },
        renderChannelSpecificFieldHeaders: function() {
            for (var fieldName in this.props.channelSpecificFields) {
                var fieldDisplayName = this.props.channelSpecificFields[fieldName]['displayName']
                    ? this.props.channelSpecificFields[fieldName]['displayName']
                    : fieldName;
                return <td>
                    {fieldDisplayName}
                </td>
            }
        },
        renderChannelSpecificFields: function(variationId) {
            for (var fieldName in this.props.channelSpecificFields) {
                var value = this.state.variationsFormState[variationId] && this.state.variationsFormState[variationId][fieldName]
                    ? this.state.variationsFormState[variationId][fieldName]
                    : null;

                var elementForChannelSpecificField = this.props.channelSpecificFields[fieldName].getFormComponent(
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
                        <tr>
                            <td><Checkbox onClick={this.onCheckAll} isChecked={this.state.allChecked} /></td>
                            <td>sku</td>
                            {this.renderAttributeHeaders()}
                            <td>price</td>
                            {this.renderChannelSpecificFieldHeaders()}
                        </tr>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return VariationPicker;
});
