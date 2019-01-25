"use strict";

let globalActions = (function() {
    return {
        changeView: () => {
            return {
                type: "VIEW_CHANGE",
                payload: {}
            }
        }
    }
})();

export default globalActions;