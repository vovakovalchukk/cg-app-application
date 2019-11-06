define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract',
    'InvoiceDesigner/Constants'
], function(
    requireModule,
    DomListenerAbstract,
    Constants
) {
    let selectNode = null;

    let TemplateType = function() {
        DomListenerAbstract.call(this);
    };

    TemplateType.prototype = Object.create(DomListenerAbstract.prototype);

    TemplateType.prototype.init = function(module) {
        DomListenerAbstract.prototype.init.call(this, module);

        selectNode = document.getElementById(Constants.TEMPLATE_TYPE_DROPDOWN_ID);

        $(document).on('change', `#${Constants.TEMPLATE_TYPE_DROPDOWN_ID}`, (event, selectBox, value) => {
            this.getModule().templateTypeSelectionMade(value);
        });
    };

    TemplateType.prototype.getSelectNode = function() {
        return selectNode;
    };

    return TemplateType;
});