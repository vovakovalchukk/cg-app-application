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

    Cancel.prototype.init = function(templateMap) {
        popup = new Popup(templateMap, {
            title: this.getType() + " Reason",
            type: this.getType()
        }, "popup");

        var that = this;
        popup.getElement().on('mustacheRender', function(event, cgmustache, templates, data, templateId) {
            var reasons = [];
            $.each(that.getReasons(), function(index, reason) {
                reasons.push({
                    title: reason
                });
            });

            data['reasons'] = cgmustache.renderTemplate(
                templates,
                {
                    name: 'reasons',
                    class: 'popup-cancel-drop-down',
                    options: reasons
                },
                'select'
            );
        });
    };

    Cancel.prototype.action = function(element) {
        this.listen(popup);
        popup.show();
    };

    Cancel.prototype.listen = function(popup) {
        var that = this;
        popup.getElement().off("click", ".popup-cancel-button").on("click", ".popup-cancel-button", function () {
            var reason = popup.getElement().find(".popup-cancel-drop-down:input").val();
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