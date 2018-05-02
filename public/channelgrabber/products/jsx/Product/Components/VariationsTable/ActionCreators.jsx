define([], function() {
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
                }
            }
        ;

        return ActionCreators;

        function getVariationValues(currState, variationId) {
            var formHasValues = currState.form.createProductForm.values;

            if (formHasValues) {
                var formHasVariationValues = currState.form.createProductForm.values.variations;
                if (formHasVariationValues) {

                    var variationValues = currState.form.createProductForm.values.variations["variation-" + variationId];
                    if (!variationValues) {
                        return false;
                    }

                    return variationValues;

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