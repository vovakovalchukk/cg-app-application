define(['BulkActionAbstract', 'popup/mustache', 'element/FileUploadAbstract'], function(BulkActionAbstract, Popup, FileUpload) {
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

        var fileUpload;
        this.setFileUpload = function(newFileUpload) {
            fileUpload = newFileUpload;
            return this;
        };

        this.getFileUpload = function() {
            return fileUpload;
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

        var fileUpload = new FileUpload(
            $,
            "#popup-stock-import-file-upload-input",
            ".popup-stock-import-file-button",
            ".popup-stock-import",
            popup.getElement()
        );
        this.setFileUpload(fileUpload);

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
        });

        this.listen(popup, fileUpload);
    };

    StockImport.prototype.invoke = function() {
        this.getPopup().show();
    };

    StockImport.prototype.listen = function(popup, fileUpload) {
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
        fileUpload.watchForFileSelection(function(file) {
            $(".popup-stock-import-file-name", popup.getElement()).html("<img src=\"cg-built/zf2-v4-ui/img/loading-transparent.gif\" >");

            var reader = new FileReader();
            reader.readAsText(file);
            reader.onloadend = function(event) {
                $("#popup-stock-import-file-upload-hidden-input", popup.getElement()).val(event.target.result);
                $(".popup-stock-import-file-name", popup.getElement()).html(file.name);
            };
        });
    };

    return StockImport;
});
