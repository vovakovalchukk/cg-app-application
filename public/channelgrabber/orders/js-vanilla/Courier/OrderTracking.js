import Confirm from 'popup/confirm';

const ENTER_KEY_CODE = 13;

const MESSAGE_NOTICE_UPDATING = 'Updating tracking info';
const MESSAGE_CONFIRM_USED = 'You have used this tracking number before, you may have an error when dispatching. Do you wish to continue using this tracking number?';
const MESSAGE_SUCCESS = 'Tracking info updated';

const orderTracking = () => {
    $(document).on('keypress', '.order-tracking-number', function (event) {
        if (parseInt(event.which) !== ENTER_KEY_CODE) {
            return;
        }

        handleOrderTrackingSave($(this));
    });
};

export default orderTracking;

const handleOrderTrackingSave = (currentInput) => {
    const postData = buildPostData(currentInput);
    const orderId = getOrderId(currentInput);

    if (postData === false || !orderId) {
        return;
    }

    validateOrderTracking(postData, orderId);
};

const buildPostData = (currentInput) => {
    const trackingNumber = currentInput.val();
    const carrier = currentInput.parent().find('input.order-courier-select').val();

    if (!trackingNumber || !carrier) {
        return false;
    }

    return {
        carrier,
        trackingNumber
    };
};

const getOrderId = (currentInput) => {
    return currentInput.attr('id').replace('tracking-number-', '');
};

const validateOrderTracking = (postData, orderId) => {
    const validateUrl = `/orders/${orderId}/tracking/validate`;
    n.notice(MESSAGE_NOTICE_UPDATING);

    $.ajax({
        url: validateUrl,
        type: 'POST',
        data: postData,
        dataType : 'json',
        success: (data) => {
            if (data.valid) {
                saveOrderTracking(postData, orderId);
                return;
            }

            showConfirmation(postData, orderId);
        },
        error: handleAjaxError
    });
};

const saveOrderTracking = (postData, orderId) => {
    const saveUrl = `/orders/${orderId}/tracking/update`;

    $.ajax({
        url: saveUrl,
        type: 'POST',
        data: postData,
        dataType : 'json',
        success: () => {
            n.success(MESSAGE_SUCCESS);
        },
        error: handleAjaxError
    });
};

const showConfirmation = (postData, orderId) => {
    n.clearNotifications();
    new Confirm(
        MESSAGE_CONFIRM_USED,
        (answer) => {
            if (answer !== Confirm.VALUE_YES) {
                return;
            }

            saveOrderTracking(postData, orderId);
        }
    );
};

const handleAjaxError = (error, textStatus, errorThrown) => {
    return n.ajaxError(error, textStatus, errorThrown);
};
