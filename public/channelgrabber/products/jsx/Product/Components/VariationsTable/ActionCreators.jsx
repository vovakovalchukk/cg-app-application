define([], function() {
        var uniqueKey = 0;

        var ActionCreators = {
            newVariationRowCreate: function() {
                return {
                    type: 'NEW_VARIATION_ROW_CREATE'
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
            attributeColumnNameChange: function(fieldName, newValue){
                console.log("in attributeCOlumnNameCHange with fieldName: " , fieldName, ' and newValue: ', newValue);
                return {
                    type: 'ATTRIBUTE_COLUMN_NAME_CHANGE',
                    payload: {
                        fieldName: fieldName,
                        newValue:newValue
                    }
                };
            },
            newVariationRowCreateRequest: function(variationId) {
                return function(dispatch, getState) {
                    var currState = getState();
                    if (!variationHasValues(currState, variationId)) {
                        dispatch(ActionCreators.newVariationRowCreate());
                    }
                }
            },
            addNewOptionForAttribute: function(option,attributeColumnName) {
                return {
                    type: 'ATTRIBUTE_COLUMN_OPTION_ADD',
                    payload: {
                        option: option,
                        attributeColumnName: attributeColumnName
                    }
                };
            }
        };

        return ActionCreators;

        function variationHasValues(currState, variationId) {
            var formHasValues = currState.form.createProductForm.values;

            if (formHasValues) {
                var formHasVariationValues = currState.form.createProductForm.values.variations;
                if (formHasVariationValues) {
                    var variationHasValues = currState.form.createProductForm.values.variations["variation-" + variationId];
                    if (variationHasValues) {
                        return true;
                    }
                }
            } else {
                return false;
            }
        }

        function generateUniqueKey() {
            return uniqueKey++;
        }
    }
);