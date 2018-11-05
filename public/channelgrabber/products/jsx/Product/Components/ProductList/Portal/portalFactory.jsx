import React from 'react';
import ReactDOM from 'react-dom';

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

            return ReactDOM.createPortal(
                <ComponentInWrapper/>,
                portalSettings.domNodeForSubmits
            )
        }
    };

}());

export default portalFactory