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
                        '/url-goes-here',
                        {
                            id: id,
                            detail: detail,
                            value: value,
                            sku: sku
                        },
                        function() {
                            notifications.success('Saved product ' + detail + '');
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
