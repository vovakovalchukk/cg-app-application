define([], function() {
    function creator(initialState, handlerMap) {
        return function reducer(state, action) {
            if (state == undefined) {
                state = initialState;
            }
            if (handlerMap.hasOwnProperty(action.type)) {
                return handlerMap[action.type](state, action);
            }
            return state;
        }
    }

    return creator;
});