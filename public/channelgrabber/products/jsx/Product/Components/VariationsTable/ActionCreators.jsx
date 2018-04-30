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
                newVariationRowCreateRequest: function(variationId) {
                    return function(dispatch, getState) {
                        var currState = getState();
                        var variationValues = getVariationValues(currState, variationId);
                        if (!variationValues) {
                            dispatch(ActionCreators.newVariationRowCreate());
                        } else {
                            var nonDimensionalValues = getNonDimensionalVariationFields(variationValues,currState.variationsTable.fields);
                            if (nonDimensionalValues.length==0) {
                                console.log('no nonDimensional values so dispatching')
                                dispatch(ActionCreators.newVariationRowCreate());
                            }
                        }
                    }
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

        function getNonDimensionalVariationFields(values, fields) {
            var fieldsToReturn = [];
            for (var field in values) {
                if (!isDimensionField(field, fields)) {
                    console.log('field : ', fields, ' is dimension field so returning true');
                    fieldsToReturn.push(field)
                }

            }
            return fieldsToReturn;
        }

        function isDimensionField(field, fields) {
//            console.log('in isDimensionsField with field: ', field , ' and fields: ', fields);
            for (var i = 0; i < fields.length; i++) {
                if (fields[i].name == field) {
                    if (fields[i].isDimensionsField) {
                        console.log('fields[i]: ', fields[i], ' is dimension field : ', fields[i].isDimensionsField);
                        return true;
                    }
                }
            }
        }

        function generateUniqueKey() {
            return uniqueKey++;
        }
    }
);