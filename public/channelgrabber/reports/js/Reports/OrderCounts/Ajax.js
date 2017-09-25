'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

define(['AjaxRequester'], function (requester) {
    var Ajax = function () {
        function Ajax(requester) {
            _classCallCheck(this, Ajax);

            this.requester = requester;

            this.URL_ORDER_COUNTS = '/sales/orderCounts';

            this.AJAX_ERROR = 'There was an error while fetching the order data';
        }

        _createClass(Ajax, [{
            key: 'fetch',
            value: function fetch(requestData, callback) {
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
        }]);

        return Ajax;
    }();

    return new Ajax(requester);
});
