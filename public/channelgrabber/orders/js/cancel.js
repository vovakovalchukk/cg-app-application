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
    };

    Cancel.prototype.action = function(element) {
        var that = this;
        popup = new Popup("/channelgrabber/orders/template/popups/cancelOptions.html", {
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
        $('.cancel-popup-button').click(function () {
            that.getNotifications().notice("Cancelling order");
            popup.hide();
            $.ajax({
                context: that,
                url: $(that.getSelector()).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': $(that.getSelector()).data("orders"),
                    'reason': $('.cancel-popup-drop-down .text').html(),
                    'type': that.getType().toLowerCase()
                },
                success : function(data) {
                    return that.getNotifications().success("Order marked to be cancelled");
                },
                error: function(error, textStatus, errorThrown) {
                    return that.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return Cancel;
});