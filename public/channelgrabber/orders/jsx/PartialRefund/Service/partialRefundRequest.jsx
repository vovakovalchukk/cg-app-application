import React from 'react';

const partialRefundRequest = (orderId, refundReason, items, setPopupActive) => {
    n.notice('Refunding order...');
    $.ajax({
        method: 'POST',
        url: '/orders/partialRefund',
        data: {
            orderId,
            refundReason: refundReason.value,
            items: formatItemsForRequest(items)
        }
    }).fail(() => {
        n.error('There was a problem. Please try again or contact support if the issue persists.');
    }).done((data) => {
        n.success('Order marked to be partially refunded.');
        setPopupActive(false);
        window.location.reload();
    });
};

export default partialRefundRequest;

function formatItemsForRequest(items) {
    const selectedItems = [];

    Object.keys(items).forEach((itemId) => {
        const item = items[itemId];
        const amount = parseFloat(item.selectedAmount);
        if (amount === 0) {
            return false;
        }

        selectedItems.push({
            id: itemId,
            amount: item.selectedAmount
        });
    });

    return selectedItems;
}