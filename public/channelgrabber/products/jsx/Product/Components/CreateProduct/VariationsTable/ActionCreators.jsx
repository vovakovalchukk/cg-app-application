import {change as reduxFormChange} from 'redux-form';
import stateFilters from 'Product/Components/CreateProduct/functions/stateFilters';
        

        var uniqueKey = 1;

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
            setDefaultValuesForNewVariations: function(newVariationId) {
                return function(dispatch, getState) {
                    setDefaultStockModeValues(dispatch, newVariationId);
                    setDimensionFieldsFromFirstRow(dispatch, getState(), newVariationId);
                };
            }
        };

        export default ActionCreators;

        function setDimensionFieldsFromFirstRow(dispatch, state, newVariationId) {
            if (!state.form.createProductForm.values || !state.form.createProductForm.values.variations) {
                return;
            }
            var firstRowVariationValues = getFirstRowVariationValues(state.form.createProductForm.values.variations);
            var firstRowDimensionOnlyValues = stateFilters.getDimensionOnlyFieldsFromVariationRow(firstRowVariationValues, state.variationsTable.fields);
            for (var variationProperty in firstRowDimensionOnlyValues) {
                dispatch(
                    reduxFormChange(
                        'createProductForm',
                        'variations.variation-' + newVariationId.toString() + "." + variationProperty,
                        firstRowDimensionOnlyValues[variationProperty]
                    )
                );
            }
        }
        function setDefaultStockModeValues(dispatch, variationId) {
            dispatch(
                reduxFormChange(
                    'createProductForm',
                    'variations.variation-' + variationId.toString() + "." + 'stockModeType',
                    'all'
                )
            );
        }
        function getFirstRowVariationValues(variations) {
            var sortedVariationIdentifiers = Object.keys(variations).sort();
            return variations[sortedVariationIdentifiers[0]];
        }
        function generateUniqueKey() {
            return uniqueKey++;
        }
    