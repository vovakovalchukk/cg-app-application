define(['BulkActionAbstract', 'popup/mustache'], function(BulkActionAbstract, Popup) {
    var StockImport = function(selector, updateOptions) {
        BulkActionAbstract.call(this);

        this.getSelector = function() {
            return selector;
        };

        this.getUpdateOptions = function() {
            return updateOptions;
        };

        var popup;
        this.setPopup = function(newPopup) {
            popup = newPopup;
            return this;
        };

        this.getPopup = function() {
            return popup;
        };
    };

    StockImport.prototype = Object.create(BulkActionAbstract.prototype);

    StockImport.prototype.init = function(templateMap) {
        BulkActionAbstract.prototype.init.call(this, this.getSelector());

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

    StockImport.prototype.invoke = function() {
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
                that.getNotificationHandler().notice("Select a CSV file to upload");
                return;
            }

            var data = new FormData();
            data.append('updateOption', updateOption);

            $.each(files, function(key, value) {
                data.append(key, value);
            });

            that.getNotificationHandler().notice("Uploading stock levels");
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
                    that.getNotificationHandler().success("Uploading stock CSV...");
                },
                error: function(error, textStatus, errorThrown) {
                    that.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return StockImport;
});
