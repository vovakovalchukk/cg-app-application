import React from 'react';
import ReactDOM from 'react-dom';

let portalFactory = (function() {
    return {
        createPortal: function(paramObj) {
            let {
                portalSettings,
                Component,
                componentProps,
            } = paramObj;

            return ReactDOM.createPortal(
                (
                    <portalSettings.PortalWrapper>
                        <Component {...componentProps} />
                    </portalSettings.PortalWrapper>
                ),
                portalSettings.domNodeForSubmits
            )
        }
    };

}());

export default portalFactory