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

        this.getNoticeMessage = function() {
            return "Uploading stock levels";
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

    StockImport.prototype.init = function(templateMap, selector) {
        this.setSelector(selector);

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

        this.listen(popup);
    };

    StockImport.prototype.action = function() {
        this.getPopup().show();
    };

    StockImport.prototype.listen = function(popup) {
        var that = this;
        popup.getElement().on("click", ".popup-stock-import-button", function () {
            var updateOption = popup.getElement().find(".popup-stock-import-drop-down:input").val();
            if (!updateOption.length) {
                return;
            }

            var filesElement = popup.getElement().find("#popup-stock-import-file-upload-input")[0];
            var files = filesElement.files;
            if ((typeof files === "undefined") || !files.length) {
                that.getNotifications().notice("Select a CSV file to upload");
                return;
            }

            var data = new FormData();
            data.append('updateOption', updateOption);

            $.each(files, function(key, value) {
                data.append(key, value);
            });

            that.getNotifications().notice(that.getNoticeMessage());
            popup.hide();

            $.ajax({
                context: that,
                url: $(that.getSelector()).data("url"),
                type: "POST",
                dataType: 'json',
                data: data,
                processData: false,
                contentType: false,
                success : function() {
                    that.getNotifications().success("Uploading stock CSV...");
                },
                error: function(error, textStatus, errorThrown) {
                    that.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return StockImport;
});
