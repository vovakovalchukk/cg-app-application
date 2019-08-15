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
        this.initialiseMultiPageInputs(template);
    };

    MultiPage.prototype.initialiseMultiPageInputs = function(template) {
        let multiPageData = template.getMultiPage().getData();
        let inputs = this.getDomListener().getInputs();
        for (let property in multiPageData) {
            let input = inputs[property];
            let value = multiPageData[property];
            if (!input || !value) {
                continue;
            }
            domManipulator.setValueToInput(input, value);
        }
    };

    MultiPage.prototype.setTrack = function(value, track) {
        const template = this.getTemplate();
        const multiPage = this.getTemplate().getMultiPage();
        const inputs = this.getDomListener().getInputs();

        const dimensionProperty = multiPage.getRelevantDimensionFromTrack(track);
        const maxDimensionValue = multiPage.calculateMaxDimensionValue(template, dimensionProperty, value);

        domManipulator.setValueToInput(inputs[dimensionProperty], maxDimensionValue);

        multiPage.setMultiple({
            [track]: value,
            [dimensionProperty]: maxDimensionValue
        });
    };

    MultiPage.prototype.setDimension = function(dimension, value) {
        this.getTemplate().getMultiPage().setDimension(dimension, value);
    };

    return new MultiPage();
});