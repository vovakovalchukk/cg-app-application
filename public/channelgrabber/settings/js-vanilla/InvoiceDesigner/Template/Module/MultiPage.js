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
        let templateDomManipulator = template.getDomManipulator();

        $(document).on(
            templateDomManipulator.getTemplateChangedEvent(),
            this.reactToTemplateChange.bind(this)
        );
    };

    MultiPage.prototype.reactToTemplateChange = function(event, template, performedUpdates) {
        if (!performedUpdates) {
            return;
        }
        let multiPageUpdate = performedUpdates.find(update => (
            update.entity === template.getMultiPage().getEntityName()
        ));
        if (!multiPageUpdate) {
            return;
        }

        let inputs = this.getDomListener().getInputs();
        let inputToChange = inputs[multiPageUpdate.field];
        let valueToApply = multiPageUpdate.value;
        if (!inputToChange || (typeof valueToApply === "undefined")) {
            return;
        }
        domManipulator.setValueToInput(inputToChange, valueToApply);
    };

    MultiPage.prototype.initialiseMultiPageInputs = function(template) {
        const multiPage = template.getMultiPage();
        const multiPageData = multiPage.getData();
        const inputs = this.getDomListener().getInputs();

        for (let property in multiPageData) {
            let input = inputs[property];
            let value = multiPageData[property];
            if (!input || !value) {
                continue;
            }
            domManipulator.setValueToInput(input, value);
        }
    };

    MultiPage.prototype.setGridTrack = function(value, gridTrack) {
        const template = this.getTemplate();
        const multiPage = this.getTemplate().getMultiPage();
        const inputs = this.getDomListener().getInputs();

        const dimensionProperty = multiPage.getRelevantDimensionFromGridTrack(gridTrack);
        const maxDimensionValue = multiPage.calculateMaxDimensionValue(template, dimensionProperty, value);

        domManipulator.setValueToInput(inputs[dimensionProperty], maxDimensionValue);

        multiPage.setMultiple({
            [gridTrack]: value,
            [dimensionProperty]: maxDimensionValue
        });
    };

    MultiPage.prototype.setDimension = function(dimension, value) {
        this.getTemplate().getMultiPage().setDimension(dimension, value);
    };

    return new MultiPage();
});