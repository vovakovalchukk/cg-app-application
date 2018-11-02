import React from 'react';
import ReactDOM from 'react-dom';
import constants from "../Config/constants";
import utility from "../utility";
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes'

let portalFactory = (function() {
    return {
        createPortal: function(paramObj) {
            let {
                portalSettings,
                Component,
                componentProps
            } = paramObj;

            let ComponentInWrapper = () => {
                return (
                    <portalSettings.PortalWrapper>
                       <Component
                           {...componentProps}
                       />
                    </portalSettings.PortalWrapper>
                );
            };

//            console.log('in createPortal with portalSettings: ', portalSettings);
            return ReactDOM.createPortal(
                <ComponentInWrapper/>,
                portalSettings.domNodeForSubmits
            )
        }
    };

}());

export default portalFactory