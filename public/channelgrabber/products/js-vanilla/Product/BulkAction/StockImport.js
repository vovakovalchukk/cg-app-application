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

        var self = this;
        popup.getElement().on('mustacheRender', function(event, cgmustache, templates, data, templateId) {
            var updateOptions = self.getUpdateOptions();

            data['updateOptions'] = cgmustache.renderTemplate(
                templates,
                {
                    name: 'updateOptions',
                    class: 'popup-stock-import-drop-down',
                    options: updateOptions
                },
                'select'
            );

            data['fileUpload'] = cgmustache.renderTemplate(
                templates,
                {
                    id: 'popup-stock-import-file-upload',
                    name: 'stockFileUpload',
                    class: 'popup-stock-import-file'
                },
                'fileUpload'
            );
        });

        this.listen(popup);
    };

    StockImport.prototype.invoke = function() {
        this.getPopup().show();
    };

    StockImport.prototype.listen = function(popup) {
        var self = this;
        popup.getElement().on("click", ".popup-stock-import-button", function () {
            var updateOption = popup.getElement().find(".popup-stock-import-drop-down:input").val();
            if (!updateOption.length) {
                return;
            }

            var filesElement = popup.getElement().find("#popup-stock-import-file-upload-hidden-input");
            var fileContent = filesElement.val();
            if ((typeof fileContent === "undefined") || !fileContent.length) {
                return;
            }

            var data = new FormData();
            data.append('updateOption', updateOption);
            data.append('stockUploadFile', fileContent);

            self.getNotificationHandler().notice("Uploading stock levels");
            popup.hide();

            $.ajax({
                context: self,
                url: $(self.getSelector()).data("url"),
                type: "POST",
                dataType: 'json',
                data: data,
                processData: false,
                contentType: false,
                success : function() {
                    self.getNotificationHandler().success("Uploading stock CSV...");
                },
                error: function(error, textStatus, errorThrown) {
                    self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return StockImport;
});
