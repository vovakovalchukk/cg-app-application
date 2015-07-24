
define(['popup/mustache'], function(Popup) {
    var StockImport = function(notifications, updateOptions) {
        var selector;
        var popup;

        this.getNotifications = function() {
            return notifications;
        };

        this.getUpdateOptions = function() {
            return updateOptions;
        };

        this.getSelector = function() {
            return selector;
        };

        this.setSelector = function(newSelector) {
            selector = newSelector;
            return this;
        };

        this.getPopup = function() {
            return popup;
        };

        this.setPopup = function(newPopup) {
            popup = newPopup;
            return this;
        };
    };

    StockImport.prototype.init = function(templateMap) {
        var popup = new Popup(templateMap, {
            title: "Update Option",
            type: "StockImport"
        }, "popup");
        this.setPopup(popup);

        var that = this;
        popup.getElement().on('mustacheRender', function(event, cgmustache, templates, data, templateId) {
            var updateOptions = that.getUpdateOptions();

            data['updateOptions'] = cgmustache.renderTemplate(
                templates,
                {
                    name: 'updateOptions',
                    class: 'popup-stock-import-drop-down',
                    options: updateOptions
                },
                'select'
            );
        });
    };

    StockImport.prototype.action = function() {
        var popup = this.getPopup();
        this.listen(popup);
        popup.show();
    };

    StockImport.prototype.listen = function(popup) {
        var that = this;
        popup.getElement().on("click", ".popup-stock-import-button", function () {
            var updateOption = popup.getElement().find(".popup-stock-import-drop-down:input").val();
            if (!updateOption.length) {
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
                    'updateOption': updateOption,
                },
                success : function(data) {
                    return that.getNotifications().success("Uploading stock CSV...");
                },
                error: function(error, textStatus, errorThrown) {
                    return that.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return StockImport;
});
