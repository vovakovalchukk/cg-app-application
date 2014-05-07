define(['popup/mustache'], function(Popup) {
    var Cancel = function(notifications, reasons, type) {
        var selector;

        this.getNotifications = function() {
            return notifications;
        };

        this.getReasons = function() {
            return reasons;
        };

        this.getType = function() {
            return type;
        };

        this.getSelector = function() {
            return selector;
        };

        this.setSelector = function(newSelector) {
            selector = newSelector;
            return this;
        };

        this.getNoticeMessage = function() {
            var noticeMessage = {
                'Cancel' : 'Cancelling order',
                'Refund' : 'Refunding order'
            };
            return noticeMessage[this.getType()];
        };

        this.getSuccessMessage = function() {
            var successMessage = {
                'Cancel' : 'Order marked to be cancelled',
                'Refund' : 'Order marked to be refunded'
            };
            return successMessage[this.getType()];
        };
    };

    Cancel.prototype.action = function(element) {
        var that = this;
        popup = new Popup($(this.getSelector()).attr('data-popup'), {
            title: this.getType() + " Reason",
            reasons: function(){
                var mappedReasons = [];
                $.each(that.getReasons(), function(key, value) {
                    mappedReasons.push({name: value});
                });
                return mappedReasons;
            },
            type: this.getType()
        });
        popup.show();
        this.listen();
    };


    Cancel.prototype.listen = function() {
        var that = this;
        $('.popup-cancel-button').click(function () {
            var reason = $('.popup-cancel-drop-down .text').html();
            if (!reason.length) {
                return;
            }

            that.getNotifications().notice(that.getNoticeMessage());
            popup.hide();
            $.ajax({
                context: that,
                url: $(that.getSelector()).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': $(that.getSelector()).data("orders"),
                    'reason': reason,
                    'type': that.getType().toLowerCase()
                },
                success : function(data) {
                    return that.getNotifications().success(that.getSuccessMessage());
                },
                error: function(error, textStatus, errorThrown) {
                    return that.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return Cancel;
});