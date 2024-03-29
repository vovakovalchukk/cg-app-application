import focusActions from 'Product/Components/ProductList/ActionCreators/focusActions';

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
        changeName: (newName, productId) => {
            return {
                type: "NAME_CHANGE",
                payload: {
                    newName,
                    productId
                }
            }
        },
        nameBlur : productId => {
            return async (dispatch,getState) => {
                dispatch(focusActions.blurInput());
                let productName = getState().name.names.byProductId[productId];
                let newName = productName.value;

                if(newName === productName.originalValue){
                    return;
                }

                dispatch({type: "NAME_UPDATE_START"});
                let response = await updateNameAjax(productId, newName);

                if(response.error){
                    return dispatch({
                        type: "NAME_UPDATE_ERROR",
                        payload: {
                            error: response.error
                        }
                    })
                }
                return dispatch({
                    type: "NAME_UPDATE_SUCCESS",
                    payload: {
                        productId,
                        newName
                    }
                })
            }
        },
        cancelNameEdit: productId => {
            return {
                type: "NAME_EDIT_CANCEL",
                payload: {
                    productId
                }
            }
        }
    };
})();

export default nameActions;

async function updateNameAjax(productId, newName) {
    return new Promise((resolve) => {
        $.ajax({
            url: 'products/name',
            type: 'POST',
            dataType: 'json',
            data: {id: productId, name: newName},
            success: response => {
                return resolve({status: 'success', response})
            },
            error: error => {
                return resolve({status: 'error', error})
            }
        });
    });
}