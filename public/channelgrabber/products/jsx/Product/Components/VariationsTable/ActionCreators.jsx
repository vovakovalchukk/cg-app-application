define([
        'redux-form',
        'Product/Components/CreateProduct/functions/stateFilters'

    ], function(
    ReduxForm,
    stateFilters
    ) {
        var uniqueKey = 0;

        var ActionCreators = {
            newVariationRowCreate: function() {
                return {
                    type: 'NEW_VARIATION_ROW_CREATE'
                }
            },
            variationRowRemove: function(variationId) {
                return {
                    type: 'VARIATION_ROW_REMOVE',
                    payload: {
                        variationId: variationId
                    }
                }
            },
            newAttributeColumnRequest: function() {
                return {
                    type: 'NEW_ATTRIBUTE_COLUMN_REQUEST',
                    payload: {
                        uniqueNameKey: generateUniqueKey()
                    }
                };
            },
            attributeColumnRemove: function(fieldName) {
                return {
                    type: 'ATTRIBUTE_COLUMN_REMOVE',
                    payload: {
                        fieldName: fieldName
                    }
                };
            },
            attributeColumnNameChange: function(fieldName, newValue) {
                return {
                    type: 'ATTRIBUTE_COLUMN_NAME_CHANGE',
                    payload: {
                        fieldName: fieldName,
                        newValue: newValue
                    }
                };
            },
            addNewOptionForAttribute: function(option, attributeColumnName) {
                return {
                    type: 'ATTRIBUTE_COLUMN_OPTION_ADD',
                    payload: {
                        option: option,
                        attributeColumnName: attributeColumnName
                    }
                };
            },
            setNewVariationDimensions: function(newVariationId) {
                return function(dispatch, getState) {
                    var state = getState();
                    var firstRowVariationValues = getFirstRowVariationValues(state.form.createProductForm.values.variations);
                    var firstRowDimensionOnlyValues = stateFilters.getDimensionOnlyFieldsFromVariationRow(firstRowVariationValues, state.variationsTable.fields);
                    for (var variationProperty in firstRowDimensionOnlyValues) {
                        dispatch(
                            ReduxForm.change(
                                'createProductForm',
                                'variations.variation-' + newVariationId.toString() + "." + variationProperty,
                                firstRowDimensionOnlyValues[variationProperty]
                            )
                        );
                    }
                };
            }
        };

        return ActionCreators;

        function getFirstRowVariationValues(variations) {
            var sortedVariationIdentifiers = Object.keys(variations).sort();
            return variations[sortedVariationIdentifiers[0]];
        }

        function generateUniqueKey() {
            return uniqueKey++;
        }
    }
);