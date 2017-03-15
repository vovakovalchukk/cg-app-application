define([
    'popup/confirm'
], function(
    Confirm
) {
    var Order = function()
    {
        var init = function()
        {
            var self = this;

            $(document).on('change', Order.SELECTOR, function(event, data){
                if (data === undefined) {
                    self.save();
                    return;
                }

                var selection = data[0].innerText.trim();

                if (selection !== 'Do not auto archive') {
                    self.save();
                    return;
                }

                var message = "If you disable auto archiving of orders this may affect the performance of your OrderHub account as time goes by. " +
                    "Are you sure you want to disable auto archiving?";
                var confirm = new Confirm(message, function(answer)
                {
                    if (answer == Confirm.VALUE_YES) {
                        self.save();
                    }
                });
                confirm.show();
            });
        };
        init.call(this);
    };

    Order.SELECTOR = '.order-management-form';
    Order.ARCHIVE_TIME_SELECTOR = '#autoArchiveTimeframe-custom-select input';
    Order.DISPATCH_ORDER_CHECKBOX_SELECTOR = '#dispatch-order-warning-checkbox';
    Order.ETAG_SELECTOR = '#order-eTag';

    Order.prototype.save = function()
    {
        n.notice('Saving order management settings');
        var orderSettings = {
            "eTag": $(Order.ETAG_SELECTOR).val(),
            "autoArchiveTimeframe": $(Order.ARCHIVE_TIME_SELECTOR).val(),
            "dispatchOrderWarning": $(Order.DISPATCH_ORDER_CHECKBOX_SELECTOR).prop('checked')  ? '1' : '0'
        };

        var self = this;
        $.ajax({
            url: "orders/save",
            type: "POST",
            dataType : 'json',
            data: orderSettings
        }).success(function(data) {
            if(data['eTag']) {
                $(Order.ETAG_SELECTOR).val(data['eTag']);
            }
            n.success('Saved order management settings');
        }).error(function(error, textStatus, errorThrown) {
            n.ajaxError(error, textStatus, errorThrown);
        });
    };

    return Order;
});