define([
    'Orders/OrdersBulkActionAbstract',
    'popup/mustache',
    'Orders/SaveCheckboxes',
    'mustache'
],function(
    OrdersBulkActionAbstract,
    Popup,
    saveCheckboxes,
    Mustache
) {
    var TagPopup = function(notifications, popupTemplate)
    {
        OrdersBulkActionAbstract.call(this);

        var popup;

        this.getPopup = function() {
            return popup;
        };
        
        this.getSaveCheckboxes = function()
        {
            return saveCheckboxes;
        };

        var init = function() {
            var self = this;
            popup = new Popup(popupTemplate);

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
        init.call(this);
    };

    TagPopup.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    TagPopup.prototype.getAppendUrl = function(element)
    {
        return this.getUrl('append', element);
    };

    TagPopup.prototype.getRemoveUrl = function(element)
    {
        return this.getUrl('remove', element);
    };

    TagPopup.prototype.getUrl = function(action, element)
    {
        element = (element ? element: this.getElement());
        return Mustache.render(element.data("url"), {action: action});
    };

    TagPopup.prototype.saveTag = function(tagName)
    {
        var datatable = this.getDataTableElement();
        var data = this.getDataToSubmit();
        this.apply(
            this.getAppendUrl(),
            tagName,
            data,
            {
                complete: function() {
                    if (!datatable.length) {
                        return;
                    }
                    datatable.cgDataTable("redraw");
                    saveCheckboxes.refreshCheckboxes(datatable);
                }
            }
        );
    };

    TagPopup.prototype.invoke = function()
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }

        var tag = this.getElement().data('tag');
        if (tag) {
            return this.saveTag(tag);
        }

        this.getPopup().show();
    };

    // This method is outside of the bulk actions, it is used by the tag-specific columns,
    // see orders/orders/table/header/tag.phtml
    TagPopup.prototype.checkbox = function(checkbox)
    {
        var tag = checkbox.data('tag');
        var orders = checkbox.data('orders');
        if (!tag || !orders || !orders.length) {
            return;
        }

        var url;
        if (checkbox.is(":checked")) {
            url = this.getAppendUrl(checkbox);
        } else {
            url = this.getRemoveUrl(checkbox);
        }

        this.apply(
            url,
            tag,
            {"orders": orders},
            {
                complete: function() {
                    var datatable = $(checkbox).data("datatable");
                    if (datatable) {
                        $("#" + datatable).cgDataTable("redraw");
                    }
                }
            }
        );
    };

    TagPopup.prototype.apply = function(url, tag, data, ajaxSettings)
    {
        var self = this;
        data['tag'] = tag;
        var ajax = {
            context: this,
            url: url,
            type: "POST",
            dataType: 'json',
            data: data,
            success : function(data) {
                this.setFilterId(data.filterId);
                saveCheckboxes.setSavedCheckboxes(self.getOrders());
                return self.getNotificationHandler().success("Tagged Successfully");
            },
            error: function(error, textStatus, errorThrown) {
                return self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            }
        };

        if (ajaxSettings !== undefined) {
            $.extend(ajax, ajaxSettings);
        }

        this.getNotificationHandler().notice("Updating Order Tag");
        return $.ajax(ajax);
    };

    TagPopup.prototype.savePopup = function() {
        var name = $.trim(this.getPopup().getElement().find("input#tag-name").val());
        if (!name.length) {
            return;
        }
        this.saveTag(name);
        this.getPopup().hide();
    };

    TagPopup.prototype.getOrders = function()
    {
        // If this is one of the dropdown options we have to check the main button for orders
        if (this.getElement().data('tag')) {
            var parentElement = this.getElement().closest('.custom-select').find('span.action');
            var orders = parentElement.data('orders');
            if (orders) {
                return orders;
            }
        }
        // Fall back to parent method
        return OrdersBulkActionAbstract.prototype.getOrders.call(this);
    };

    return TagPopup;
});
