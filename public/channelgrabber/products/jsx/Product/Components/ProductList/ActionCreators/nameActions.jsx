"use strict";

let nameActions = (function() {
    return {
        extractNamesFromProducts: products => {
            return {
                type: "NAMES_FROM_PRODUCTS_EXTRACT",
                payload: {
                    products
                }
            }
        },
        changeName: (productId, e) => {
            let newName = e.target.value;
            return {
                type: "NAME_CHANGE",
                payload: {
                    newName,
                    productId
                }
            }
        },
        focusName: productId => {
            return {
                type: "NAME_FOCUS",
                payload: {
                    productId
                }
            }
        },
        blurName: productId => {
            return {
                type: "NAME_BLUR",
                payload: {
                    productId
                }
            }
        }
    };
})();

export default nameActions;