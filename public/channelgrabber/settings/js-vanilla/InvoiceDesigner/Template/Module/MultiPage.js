define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/MultiPage',
    'InvoiceDesigner/Template/PaperType/Entity',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    MultiPageListener,
    PaperType,
    ElementMapperAbstract,
    domManipulator
) {
    let MultiPage = function() {
        ModuleAbstract.call(this);

        this.setDomListener(new MultiPageListener());

        this.getStorage = function() {
            return storage;
        };
    };

    MultiPage.MODULE_SELECTOR = '#multiPageModule';

    MultiPage.prototype = Object.create(ModuleAbstract.prototype);

    MultiPage.prototype.init = function(template, templateService) {
        console.log('in MultiPage .init');
        
        
        ModuleAbstract.prototype.init.call(this, template, templateService);
//        this.initialiseMarginInputs(template);
    };

    MultiPage.prototype.setRows = function(value){
        console.log('in setRows');
        
        
    };
    MultiPage.prototype.setColumns = function(value){
        console.log('in setColumns');
        
        
    };
    MultiPage.prototype.setDimension = function(dimension, value){
        console.log('in setDimension');
        
        
    };

    return new MultiPage();
});