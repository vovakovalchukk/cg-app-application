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
        var elementId = ElementMapperAbstract.getElementIdFromDomId(elementDomId);
        var element = this.getTemplate().getElements().getById(elementId);
        var format = file.type.replace('image/', '');

        this.getFileUpload().loadBinaryDataFromFile(file, function(data)
        {
            element.setFormat(format);
            element.setSource(btoa(data));

            var image = new Image();
            image.src = 'data:image/' + element.getFormat().toLowerCase() + ';base64,' + element.getSource();

            self.getDomManipulator().triggerElementResizedEvent(
                element.getId(),
                {
                    top: Number(element.getX()).mmToPx,
                    left: Number(element.getY()).mmToPx
                },
                {
                    width: image.naturalWidth,
                    height: image.naturalHeight
                }
            );
        });
    };

    return new ImageUpload();
});