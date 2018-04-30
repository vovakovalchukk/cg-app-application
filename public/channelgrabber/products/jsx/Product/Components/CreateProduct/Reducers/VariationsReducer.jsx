define([
    'Common/Reducers/creator'
], function(
    reducerCreator
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
            {tableId: 2, fieldId: 1},
            {tableId: 2, fieldId: 2},
            {tableId: 2, fieldId: 5},
            {tableId: 2, fieldId: 6},
            {tableId: 2, fieldId: 7},
            {tableId: 2, fieldId: 8}
        ],
        fields: [
            {
                id: 1,
                name: 'image',
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
                type: 'text',
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
                label: 'Weight (kg)',
                type: 'text',
                isCustomAttribute: false,
                isDimensionsField: true
            },
            {
                id: 6,
                name: 'width',
                label: 'Width (cm)',
                type: 'text',
                isCustomAttribute: false,
                isDimensionsField: true
            },
            {
                id: 7,
                name: 'heightt',
                label: 'Height (cm)',
                type: 'text',
                isCustomAttribute: false
            },
            {
                id: 8,
                name: 'depth',
                label: 'Depth (cm)',
                type: 'text',
                isCustomAttribute: false
            }
        ]
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
        "NEW_ATTRIBUTE_COLUMN_REQUEST": function(state, action) {
            var fieldsCopy = state.fields.slice();
            currentCustomFieldId++;
            fieldsCopy.push(defaultNewCustomField(action.payload.uniqueNameKey));
            var tablesFieldsCopy = state.tablesFields.slice();
            console.log("tableFieldsCopy : " , tablesFieldsCopy)
            tablesFieldsCopy.push({
                tableId: 1,
                fieldId: currentCustomFieldId
            },{
                tableId:2,
                fieldId:currentCustomFieldId
            });
            console.log('new tableFieldsCopy: ' , tablesFieldsCopy);

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
            var fieldToChange = findFieldByName(action.payload.fieldName, fieldsCopy);
            fieldToChange.label = action.payload.newValue;

            var newState = Object.assign({}, state, {
                fields: fieldsCopy
            });
            return newState;
        }

    });
    return VariationsTableReducer;

    function findFieldByName(fieldName, fields) {
        var indexOfField = fields.map(function(field) {
            return field.name;
        }).indexOf(fieldName);
        return fields[indexOfField];
    }

});