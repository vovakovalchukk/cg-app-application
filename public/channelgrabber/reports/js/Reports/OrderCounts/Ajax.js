define([
    'AjaxRequester'
], function(
    requester
) {
    class Ajax {
        constructor(requester) {
            this.requester = requester;

            this.URL_ORDER_COUNTS = '/sales/orderCounts';

            this.AJAX_ERROR = 'There was an error while fetching the order data';
        }

        fetch(requestData, callback) {
            this.requester.sendRequest(this.URL_ORDER_COUNTS, requestData, function (response) {
                if (response.data) {
                    callback(response.data);
                } else if (response.error) {
                    n.error(response.error);
                } else {
                    n.error(this.AJAX_ERROR);
                }
            }, function () {
                n.error(this.AJAX_ERROR);
            });
        }
    }

    return new Ajax(requester);
});
