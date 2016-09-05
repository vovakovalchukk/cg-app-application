define(
    ['Details/DomListener', 'DeferredQueue', 'AjaxRequester'],
    function(DomListener, DeferredQueue, ajaxRequester) {
        function Service()
        {
            var domListener = new DomListener(this);
            this.getDomListener = function()
            {
                return domListener;
            };

            var queue = new DeferredQueue();
            this.getQueue = function()
            {
                return queue;
            };

            this.getNotifications = function()
            {
                return n;
            };
        }

        Service.prototype.updateDetail = function(id, detail, value, sku)
        {
            var notifications = this.getNotifications();
            if (id == undefined && sku == undefined) {
                notifications.error('Unable to save changes to product ' + detail);
            }

            notifications.notice('Saving product ' + detail + '...');
            this.getQueue().queue(
                function() {
                    return ajaxRequester.sendRequest(
                        '/products/details/update',
                        {
                            id: id,
                            detail: detail,
                            value: value,
                            sku: sku
                        },
                        function(response) {
                            if (response.id) {
                                notifications.success('Saved product ' + detail);
                                $(DomListener.HOLDER + ' ' + DomListener.ROW)
                                    .filter(function() {
                                        return $(this).data('sku') == sku;
                                    })
                                    .data('id', response.id);
                            } else {
                                notifications.error('Unable to save changes to product ' + detail);
                            }
                        },
                        function() {
                            notifications.error('Unable to save changes to product ' + detail);
                        }
                    )
                }
            );
        };

        return Service;
    }
);
