define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";
    var initialState = {
        variations: [
            {id: 0, hasChanged: false}
        ],
        fields: [
            {
                name: 'image',
                label: 'Image',
                type:'image',
                isCustomAttribute: false
            },
            {
                name: 'sku',
                label: 'SKU',
                type:'text',
                isCustomAttribute: false
            },
            {
                name: 'quantity',
                label: 'Quantity',
                type:'text',
                isCustomAttribute: false
            },
            {
                name: 'stockMode',
                label: 'Stock Mode',
                type: 'stockModeOptions',
                isCustomAttribute: false
            }
        ]
    };
    var defaultNewCustomField = function(uniqueNameKey){
        return{
            name: 'custom-attribute-' + uniqueNameKey,
            label: '',
            type: 'customOptionsSelect',
            isCustomAttribute: true,
            options:[]
        };
    };
    var CreateVariationsTableReducer = reducerCreator(initialState, {


    });
    return CreateVariationsTableReducer;
});