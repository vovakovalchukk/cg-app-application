import reducerCreator from 'Common/Reducers/creator';
    

    var initialState = {
        isVisible: false
    };

    export default reducerCreator(initialState, {
        "ADD_NEW_CATEGORY_MAP": function() {
            return {
                isVisible: false
            };
        },
        "SHOW_ADD_NEW_CATEGORY_MAP": function() {
            return {
                isVisible: true
            };
        },
        "HIDE_NEW_CATEGORY_MAP": function() {
            return {
                isVisible: false
            };
        }
    });

