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
    };

    MultiPage.MODULE_SELECTOR = '#multiPageModule';

    MultiPage.prototype = Object.create(ModuleAbstract.prototype);

    MultiPage.prototype.init = function(template, templateService) {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        this.initialiseMultiPageInputs (template);
    };

    MultiPage.prototype.initialiseMultiPageInputs = function(template){
        let multiPageData = template.getMultiPage().getData();
        let inputs = this.getDomListener().getInputs();
        for (let property in multiPageData) {
            domManipulator.setValueToInput(inputs[property], multiPageData[property]);
        }
    };

    MultiPage.prototype.setRows = function(value){
        let template = this.getTemplate();
        let multiPage = this.getTemplate().getMultiPage();

        let height = multiPage.calculateMaxDimensionValue(template,'rows', value);

        // note - need to have this as the user might only want 2 very thin rows on the page for whatever reason
        multiPage.set('rows', value);


        multiPage.setDimension(TRACK_TO_DIMENSION['row'])

        // todo - have setRows not do a setter and simply setDimension.
        // this will set the dimension to the max without guttering.
    };

    MultiPage.prototype.setColumns = function(value){
        console.log('in setColumns');
        
        
    };

    MultiPage.prototype.setDimension = function(dimension, value){
        console.log('in setDimension');
        // note - do not need to setRows from this. this will will create circularity
        
    };

    return new MultiPage();
});