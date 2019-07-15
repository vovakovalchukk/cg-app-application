define([
    // Application Module requires here
    'InvoiceDesigner/Module/TemplateSelector'
], function(
    // Application Module variables here
    templateSelector
) {
    var Application = function()
    {
        var organisationUnitId;
        var modules = [
            // Modules here
            templateSelector
        ];

        this.getModules = function()
        {
            return modules;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            organisationUnitId = newOrganisationUnitId;
            return this;
        };
    };

    Application.prototype.init = function(organisationUnitId, templateId)
    {
        if (typeof templateId === 'undefined') {
            templateId = null;
        }
        this.setOrganisationUnitId(organisationUnitId);
        var modules = this.getModules();

        for (var key in modules) {
            modules[key].init(this, templateId);
        }
    };

    return new Application();
});