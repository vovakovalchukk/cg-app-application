import React from 'react';
import ReactDOM from 'react-dom';
import PartialRefundPopup from "./Popup";

const PartialRefundRoot = function(
    mountingNode,
    orderId,
    refundReasons,
    items
) {
    ReactDOM.render(
        <PartialRefundPopup
            orderId={orderId}
            refundReasons={refundReasons}
            items={items}
        />,
        mountingNode
    );
};

export default PartialRefundRoot;
