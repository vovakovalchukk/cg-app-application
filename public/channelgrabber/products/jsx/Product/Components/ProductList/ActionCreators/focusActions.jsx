"use strict";

let focusActions = (function() {
    return {
        focusInput: focusedInputInfo => {
            return {
                type: "INPUT_FOCUS",
                payload: {
                    focusedInputInfo
                }
            }
        },
        blurInput: () => {
            return {
                type: "INPUT_BLUR",
                payload: {}
            }
        }
    };
})();

export default focusActions;