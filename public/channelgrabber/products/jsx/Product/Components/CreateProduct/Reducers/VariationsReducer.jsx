define([
    'Common/Reducers/creator',
    'Product/Components/CreateProduct/functions/stateFilters'
], function(
    reducerCreator,
    stateFilters
) {
    "use strict";
    var initialState = {
        variations: [
            {id: 0}
        ],
        tables: [
            {
                id: 1,
                tableName: 'variations'
            },
            {
                id: 2,
                tableName: 'dimensions'
            }
        ],
        tablesFields: [
            {tableId: 1, fieldId: 1},
            {tableId: 1, fieldId: 2},
            {tableId: 1, fieldId: 3},
            {tableId: 1, fieldId: 4},
            {tableId: 1, fieldId: 9},
            {tableId: 2, fieldId: 1},
            {tableId: 2, fieldId: 2},
            {tableId: 2, fieldId: 5},
            {tableId: 2, fieldId: 6},
            {tableId: 2, fieldId: 7},
            {tableId: 2, fieldId: 8},
            {tableId: 2, fieldId: 9}
        ],
        fields: [
            {
                id: 1,
                name: 'imageId',
                label: 'Image',
                type: 'image',
                isCustomAttribute: false
            },
            {
                id: 2,
                name: 'sku',
                label: 'SKU',
                type: 'text',
                isCustomAttribute: false
            },
            {
                id: 3,
                name: 'quantity',
                label: 'Quantity',
                type: 'number',
                isCustomAttribute: false
            },
            {
                id: 4,
                name: 'stockMode',
                label: 'Stock Mode',
                type: 'stockModeOptions',
                isCustomAttribute: false
            },
            {
                id: 5,
                name: 'weight',
                label: 'Weight',
                type: 'number',
                isCustomAttribute: false,
                isDimensionsField: true,
                defaultValue: ''
            },
            {
                id: 6,
                name: 'width',
                label: 'Width',
                type: 'number',
                isCustomAttribute: false,
                isDimensionsField: true,
                defaultValue: ''
            },
            {
                id: 7,
                name: 'height',
                label: 'Height',
                type: 'number',
                isCustomAttribute: false,
                isDimensionsField: true,
                defaultValue: 'thomas'
            },
            {
                id: 8,
                name: 'length',
                label: 'Depth',
                type: 'number',
                isCustomAttribute: false,
                isDimensionsField: true,
                defaultValue: ''
            },
            {
                id: 9,
                name: 'custom-attribute-0',
                label: '',
                type: 'customOptionsSelect',
                isCustomAttribute: true,
                options: []
            }
        ],
        cells: [],
        isSubmitting: false
    };
    var currentCustomFieldId = initialState.fields.length;

    var defaultNewCustomField = function(uniqueNameKey) {
        return {
            id: currentCustomFieldId,
            name: 'custom-attribute-' + uniqueNameKey,
            label: '',
            type: 'customOptionsSelect',
            isCustomAttribute: true,
            options: []
        };
    };
    var VariationsTableReducer = reducerCreator(initialState, {
        "NEW_VARIATION_ROW_CREATE": function(state) {
            var variationsCopy = state.variations.slice();
            var newVariationId = (variationsCopy[state.variations.length - 1].id) + 1;
            variationsCopy.push({
                id: newVariationId
            });
            var newState = Object.assign({}, state, {
                variations: variationsCopy
            })
            return newState;
        },
        "VARIATION_ROW_REMOVE": function(state, action) {
            var variationsCopy = state.variations.slice();
            if (variationsCopy.length <= 1) return state;
            var indexOfVariation = variationsCopy.findIndex(function(variation) {
                return variation.id == action.payload.variationId;
            });
            if (indexOfVariation < 0) {
                return state;
            }
            variationsCopy.splice(indexOfVariation, 1);
            var newState = Object.assign({}, state, {
                variations: variationsCopy
            });
            return newState;
        },
        "NEW_ATTRIBUTE_COLUMN_REQUEST": function(state, action) {
            var fieldsCopy = state.fields.slice();
            currentCustomFieldId++;
            fieldsCopy.push(defaultNewCustomField(action.payload.uniqueNameKey));
            var tablesFieldsCopy = state.tablesFields.slice();
            tablesFieldsCopy.push({
                tableId: 1,
                fieldId: currentCustomFieldId
            }, {
                tableId: 2,
                fieldId: currentCustomFieldId - 1
            });
            var newState = Object.assign({}, state, {
                fields: fieldsCopy,
                tablesFields: tablesFieldsCopy
            });
            return newState;
        },
        "ATTRIBUTE_COLUMN_REMOVE": function(state, action) {
            var fieldsCopy = state.fields.slice();
            var indexOfField = fieldsCopy.map(function(field) {
                return field.name;
            }).indexOf(action.payload.fieldName);
            fieldsCopy.splice(indexOfField, 1);
            var newState = Object.assign({}, state, {
                fields: fieldsCopy
            });
            return newState;
        },
        "ATTRIBUTE_COLUMN_OPTION_ADD": function(state, action) {
            var fieldsCopy = state.fields.slice();
            var indexOfField = fieldsCopy.map(function(field) {
                return field.name;
            }).indexOf(action.payload.attributeColumnName);
            fieldsCopy[indexOfField].options.push(action.payload.option);
            var newState = Object.assign({}, state, {
                fields: fieldsCopy
            });
            return newState;
        },
        "ATTRIBUTE_COLUMN_NAME_CHANGE": function(state, action) {
            var fieldsCopy = state.fields.slice();
            var fieldToChange = stateFilters.findFieldByName(action.payload.fieldName, fieldsCopy);
            fieldToChange.label = action.payload.newValue;
            var newState = Object.assign({}, state, {
                fields: fieldsCopy
            });
            return newState;
        },
        "CELL_CHANGE_RECORD": function(state, action) {
            var cellsCopy = state.cells.slice();
            if (stateFilters.getCell(cellsCopy, action.payload.variationId, action.payload.fieldId)) {
                return state;
            }
            cellsCopy.push({
                variationId: action.payload.variationId,
                fieldId: action.payload.fieldId,
                hasChanged: true
            });
            var newState = Object.assign({}, state, {
                cells: cellsCopy
            });
            return newState;
        },
        "FORM_SUBMIT_REQUEST": function(state, action) {
            var newState = Object.assign({}, state, {
                isSubmitting: true
            });
            return newState;
        }
    });

    return VariationsTableReducer;
});