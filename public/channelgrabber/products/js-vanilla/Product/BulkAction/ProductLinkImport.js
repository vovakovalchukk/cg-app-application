define(['BulkActionAbstract', 'popup/mustache'], function(BulkActionAbstract, Popup) {
    var ProductLinkImport = function(selector) {
        BulkActionAbstract.call(this);

        this.getSelector = function() {
            return selector;
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

    ProductLinkImport.prototype = Object.create(BulkActionAbstract.prototype);

    ProductLinkImport.prototype.init = function(templateMap) {
        BulkActionAbstract.prototype.init.call(this, this.getSelector());

        var popup = new Popup(templateMap, {
            title: "Product Link Import",
            type: "ProductLinkImport"
        }, "popup");
        this.setPopup(popup);

        var self = this;
        popup.getElement().on('mustacheRender', function(event, cgmustache, templates, data, templateId) {

            data['fileUpload'] = cgmustache.renderTemplate(
                templates,
                {
                    id: 'popup-product-link-import-file-upload',
                    name: 'productLinkFileUpload',
                    class: 'popup-stock-import-file'
                },
                'fileUpload'
            );
        });

        this.listen(popup);
    };

    ProductLinkImport.prototype.invoke = function() {
        this.getPopup().show();
    };

    ProductLinkImport.prototype.listen = function(popup) {
        var self = this;
        popup.getElement().on("click", ".popup-stock-import-button", function () {

            var filesElement = popup.getElement().find("#popup-product-link-import-file-upload-hidden-input");
            var fileContent = filesElement.val();
            if ((typeof fileContent === "undefined") || !fileContent.length) {
                return;
            }

            var data = new FormData();
            data.append('productLinkUploadFile', fileContent);

            self.getNotificationHandler().notice("Uploading product links");
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
                    self.getNotificationHandler().success("Uploading product link CSV...");
                },
                error: function(error, textStatus, errorThrown) {
                    self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    return ProductLinkImport;
});
