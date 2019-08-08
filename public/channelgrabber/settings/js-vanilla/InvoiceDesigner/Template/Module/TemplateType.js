define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/TemplateType',
    'InvoiceDesigner/Template/PaperType/Entity',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/PrintPage/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Constants'
], function(
    ModuleAbstract,
    TemplateTypeListener,
    PaperType,
    ElementMapperAbstract,
    printPageStorage,
    domManipulator,
    Constants
) {
    let TemplateType = function() {
        ModuleAbstract.call(this);
        this.setDomListener(new TemplateTypeListener());
    };

    TemplateType.MODULE_SELECTOR = '#templateTypeModule';
    TemplateType.DEFAULT_TEMPLATE_TYPE = 'invoice';

    TemplateType.prototype = Object.create(ModuleAbstract.prototype);

    TemplateType.prototype.init = function(template, templateService) {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        this.initialiseSelect(template);
    };

    TemplateType.prototype.initialiseSelect = function(template) {
        let value = template.get('type');

        let currentTemplateType = value || TemplateType.DEFAULT_TEMPLATE_TYPE;

        this.populateTemplateTypeSelect(currentTemplateType);

        this.templateTypeSelectionMade(currentTemplateType, true);
    };

    TemplateType.prototype.populateTemplateTypeSelect = function(selected) {
        let options = Constants.TEMPLATE_TYPE_OPTIONS;
        domManipulator.populateCustomSelect(
            Constants.TEMPLATE_TYPE_DROPDOWN_ID,
            options,
            selected,
            {
                sizeClass: 'u-width-100pc'
            }
        );
    };

    TemplateType.prototype.templateTypeSelectionMade = function(value, populating) {
        if (!value || !Constants.TEMPLATE_TYPE_OPTIONS.find(option => option.value === value)) {
            return;
        }
        this.getTemplate().set('type', value, populating);
    };

    return new TemplateType();
});