define(['popup/mustache'],function(Popup) {
    var TagPopup = function(notifications, popupTemplate) {
        var self = this;
        var popup = new Popup(popupTemplate);

        this.getPopup = function() {
            return popup;
        };

        this.saveTag = function(tagName, datatable, orders) {
            apply.call(
                this,
                getAppendUrl.call(this, datatable),
                tagName,
                orders,
                {
                    complete: function() {
                        if (datatable) {
                            $("#" + datatable).cgDataTable("redraw");
                        }
                    }
                }
            );
        };

        this.action = function(event) {
            event.stopImmediatePropagation();

            var datatable = $(this).data("datatable");
            var orders = $(this).data("orders");

            if (datatable && $("#" + datatable + "-select-all").is(":checked")) {
                orders = [];
            } else {
                if (!orders && datatable) {
                    orders = $("#" + datatable).cgDataTable("selected", ".checkbox-id");
                }

                if (!orders.length) {
                    return;
                }
            }

            var tag = $(this).data("tag");
            if (tag) {
                return self.saveTag.call(this, tag, datatable, orders);
            }

            self.getPopup().show();
            self.getPopup().getElement().data('button', this);
            self.getPopup().getElement().data('datatable', datatable);
            self.getPopup().getElement().data('orders', orders);
        };

        this.checkbox = function(event) {
            event.stopImmediatePropagation();

            var tag = $(this).data("tag");
            var orders = $(this).data("orders");

            if (!tag || !orders || !orders.length) {
                return;
            }

            var url;
            if ($(this).is(":checked")) {
                url = getAppendUrl.call(this);
            } else {
                url = getRemoveUrl.call(this);
            }

            apply.call(
                this,
                url,
                tag,
                orders,
                {
                    complete: function() {
                        var datatable = $(this).data("datatable");
                        if (datatable) {
                            $("#" + datatable).cgDataTable("redraw");
                        }
                    }
                }
            );
        };

        var getAppendUrl = function(datatable) {
            return getUrl.call(this, 'append', datatable);
        };

        var getRemoveUrl = function(datatable) {
            return getUrl.call(this, 'remove', datatable);
        };

        var getUrl = function(action, datatable) {
            var url = Mustache.render($(this).data("url"), {action: action});
            if (datatable && $("#" + datatable + "-select-all").is(":checked")) {
                url += "/" + $("#" + datatable).data("filterId");
            }
            return url;
        };

        var apply = function(url, tag, orders, ajaxSettings) {
            var ajax = {
                context: this,
                url: url,
                type: "POST",
                dataType: 'json',
                data: {
                    'tag': tag,
                    'orders': orders
                },
                success : function(data) {
                    return notifications.success("Tagged Successfully");
                },
                error: function(error, textStatus, errorThrown) {
                    return notifications.ajaxError(error, textStatus, errorThrown);
                }
            };

            if (ajaxSettings !== undefined) {
                $.extend(ajax, ajaxSettings);
            }

            notifications.notice("Updating Order Tag");
            return $.ajax(ajax);
        }

        var init = function() {
            popup.getElement().on("keypress.createTag", "input#tag-name", function(event) {
                if (event.which !== 13) {
                    return;
                }
                self.savePopup.call(self);
            });

            popup.getElement().on("click.createTag", ".create", function() {
                self.savePopup.call(self);
            });

            popup.getElement().on("click.createTag", ".cancel", function() {
                popup.hide();
            });
        };
        init();
    };

    TagPopup.prototype.savePopup = function() {
        var name = $.trim(this.getPopup().getElement().find("input#tag-name").val());
        if (!name.length) {
            return;
        }
        var button = this.getPopup().getElement().data("button");
        var datatable = this.getPopup().getElement().data("datatable");
        var orders = this.getPopup().getElement().data("orders");
        this.saveTag.call(button, name, datatable, orders);
        this.getPopup().hide();
    };

    return TagPopup;
});