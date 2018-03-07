define([], function() {
    var TodoActions = {
        add: function (text) {
            return {
                type: 'ADD',
                payload: {
                    text: text
                }
            };
        },
        toggle: function (id) {
            return {
                type: 'TOGGLE',
                payload: {
                    id: id
                }
            };
        },
        visibility: function(filter) {
            return {
                type: 'SET_VISIBILITY',
                payload: {
                    filter: filter
                }
            };
        }
    };

    return TodoActions;
});