define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ImageUpload',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'element/FileUpload',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    ImageUploadListener,
    ElementMapperAbstract,
    FileUpload,
    domManipulator
) {
    var ImageUpload = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(ImageUploadListener);

        var fileUpload = new FileUpload();
        this.getFileUpload = function()
        {
            return fileUpload;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    ImageUpload.prototype = Object.create(ModuleAbstract.prototype);

    ImageUpload.prototype.imageElementFileSelected = function(elementDomId, file)
    {
        var self = this;
        var paperPage = this.getTemplate().getPaperPage();
        var elementId = ElementMapperAbstract.getElementIdFromDomId(elementDomId);
        var element = this.getTemplate().getElements().getById(elementId);
        var format = file.type.replace('image/', '');

        this.getFileUpload().loadBinaryDataFromFile(file, function(data)
        {
            var image = new Image();
            image.src = 'data:image/' + format.toLowerCase() + ';base64,' + btoa(data);
            image.onload = function() {
                self.getDomManipulator().triggerElementResizedEvent(
                    element.getId(),
                    {
                        left: Number(element.getX()).mmToPx(),
                        top: Number(element.getY()).mmToPx()
                    },
                    self.getElementSize(image, paperPage, element)
                );

                element.setFormat(format);
                element.setSource(btoa(data));
            };
        });
    };

    ImageUpload.prototype.getElementSize = function(image, paperPage, element)
    {
        var bounds = {
            width: Number(paperPage.getWidth() - element.getX()).mmToPx(),
            height: Number(paperPage.getHeight() - element.getY()).mmToPx()
        };

        var size = {
            width: image.naturalWidth,
            height: image.naturalHeight
        };

        if (size.width <= bounds.width && size.height <= bounds.height) {
            return size;
        }

        var scale = {
            width: (image.naturalWidth / image.naturalHeight) * bounds.height,
            height: (image.naturalHeight / image.naturalWidth) * bounds.width
        };

        if (scale.width >= scale.height && scale.width <= bounds.width) {
            return {
                width: scale.width,
                height: bounds.height
            };
        } else {
            return {
                width: bounds.width,
                height: scale.height
            };
        }
    };

    return new ImageUpload();
});