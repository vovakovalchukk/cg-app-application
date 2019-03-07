define(['BulkActionAbstract', 'popup/mustache', 'element/ElementCollection'], function(BulkActionAbstract, Popup, elementCollection) {
    var ProductImport = function(selector) {
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

    ProductImport.prototype = Object.create(BulkActionAbstract.prototype);

    ProductImport.FILE_UPLOAD_ID = 'popup-product-import-file';
    ProductImport.FILE_UPLOAD_NAME = 'productUploadFile';
    ProductImport.FILE_UPLOAD_CLASS = ProductImport.FILE_UPLOAD_ID;

    ProductImport.SELECTOR_BUTTON = '.popup-product-import-button';

    ProductImport.prototype.init = function(templateMap) {
        BulkActionAbstract.prototype.init.call(this, this.getSelector());
        this.listen(
            this.initPopup(templateMap)
        );
    };

    ProductImport.prototype.initPopup = function(templateMap) {
        this.setPopup(
            new Popup(
                templateMap,
                {
                    'title': 'Upload products',
                    'exampleFile': URL.createObjectURL(
                        new Blob(
                            ['"Title","SKU","Stock Quantity"' + "\r\n" + '"Example product title","EXAMPLE_SKU",2'],
                            {type : 'text/csv'}
                        )
                    ),
                    'fileUpload': {
                        'id': ProductImport.FILE_UPLOAD_ID,
                        'name': ProductImport.FILE_UPLOAD_NAME,
                        'class': ProductImport.FILE_UPLOAD_CLASS
                    }
                },
                'popup'
            )
        );
        return this.getPopup();
    };

    ProductImport.prototype.listen = function(popup) {
        var self = this;
        popup.getElement().on('click', ProductImport.SELECTOR_BUTTON, function () {
            var fileContent = elementCollection.get(ProductImport.FILE_UPLOAD_NAME).getFileContent();
            if ((typeof fileContent === 'undefined') || !fileContent.length) {
                return;
            }

            self.getNotificationHandler().notice('Uploading products');
            popup.hide();

            var data = new FormData();
            data.append('productUploadFile', fileContent);

            $.ajax({
                context: self,
                url: $(self.getSelector()).data('url'),
                type: 'POST',
                dataType: 'json',
                data: data,
                processData: false,
                contentType: false,
                success : function(response) {
                    if (!(response.status instanceof Object)) {
                        self.getNotificationHandler().error('There was a problem, please try again');
                        return;
                    }

                    if (response.status.missingHeaders.length > 0) {
                        var errors = '';
                        for (var index in response.status.missingHeaders) {
                            errors += '<li>' + response.status.missingHeaders[index] + '</li>';
                        }
                        self.getNotificationHandler().error('The uploaded file is missing the following headers:<ul>' + errors + '</ul>');
                        return;
                    }

                    if (response.status.lines.processed == 0 && response.status.lines.failed.length == 0) {
                        self.getNotificationHandler().error('The uploaded file is empty, nothing to process!');
                        return;
                    }

                    if (response.status.lines.failed.length > 0) {
                        var failed = '';
                        for (var index in response.status.lines.failed) {
                            var errors = '';
                            for (var header in response.status.lines.failed[index].errors) {
                                errors += '<dt>' + header + '</dt>';
                                errors += '<dd>' + response.status.lines.failed[index].errors[header] + '</dd>';
                            }
                            failed += '<li>Line ' + response.status.lines.failed[index].line + '<dl>' + errors + '</dl></li>';
                        }
                        var message = '';
                        if (response.status.lines.processed > 0) {
                            message += 'Creating ' + response.status.lines.processed + ' products...<br /><br />'
                        }
                        self.getNotificationHandler().error(message + 'The following lines failed to process:<ul>' + failed + '</ul>');
                        return;
                    }

                    if (response.status.lines.processed > 0) {
                        self.getNotificationHandler().success('Creating ' + response.status.lines.processed + ' products...');
                        return;
                    }

                    self.getNotificationHandler().error('Failed to process any lines');
                },
                error: function(error, textStatus, errorThrown) {
                    self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    ProductImport.prototype.invoke = function() {
        this.getPopup().show();
    };

    return ProductImport;
});