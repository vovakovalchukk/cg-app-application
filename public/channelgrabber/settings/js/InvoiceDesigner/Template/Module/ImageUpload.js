define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ImageUpload',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'element/FileUpload'
], function(
    ModuleAbstract,
    ImageUploadListener,
    ElementMapperAbstract,
    FileUpload
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
    };

    ImageUpload.prototype = Object.create(ModuleAbstract.prototype);

    ImageUpload.prototype.imageElementFileSelected = function(elementDomId, file)
    {
        var elementId = ElementMapperAbstract.getElementIdFromDomId(elementDomId);
        var element = this.getTemplate().getElements().getById(elementId);
        var format = file.type.replace('image/', '');
        this.getFileUpload().loadBinaryDataFromFile(file, function(data)
        {
            element.setFormat(format);
            element.setSource(btoa(data)); 
        });
    };

    return new ImageUpload();
});