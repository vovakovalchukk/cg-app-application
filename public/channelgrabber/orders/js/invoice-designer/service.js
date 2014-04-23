define(['./template/storage', './template/entity'], function(TemplateStorage, TemplateEntity) {
    var Service = function(storage)
    {
        var template = null;

        if (!(storage instanceof TemplateStorage)) {
            throw 'InvalidArgumentException: InvoiceDesigner Service must be passed an instance of Template Storage';
        }

        this.getStorage = function()
        {
            return storage;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            if (!(newTemplate instanceof TemplateEntity)) {
                throw 'InvalidArgumentException: InvoiceDesigner Service::setTemplate must be passed an instance of Template Entity';
            }

            template = newTemplate;
            return this;
        };
    };

    Service.instance = null;

    Service.get = function()
    {
        if (!Service.instance) {
            var storage = new TemplateStorage();
            Service.instance = new Service(storage);
        }

        return Service.instance;
    };

    Service.prototype.loadTemplate = function(id)
    {
        if (!id) {
            throw 'InvalidArgumentException: InvoiceDesigner Service::loadTemplate must be passed a template ID';
        }

        /*
         * TODO (CGIV-2002). Internally this will:
         * Make an AJAX call to load the JSON version of the template
         * Convert that to a Template Entity (via a mapper)
         * Use the data from that to fill in the template controls (name, page type, etc - perhaps via a DOM manipulator)
         * Call out to the renderer (CGIV-2026) to draw the template in the main section
         * Record where the template came from (loaded)
         */
    };

    Service.prototype.saveCurrentTemplate = function()
    {
        /*
         * TODO (CGIV-2009). Internally this will:
         * Convert the current Template Entity to its JSON representation (via a mapper)
         * Make an AJAX call to persist it (need to watch for a blank id and perform a create rather than update)
         */
    };

    Service.prototype.newTemplate = function()
    {
        /*
         * TODO (CGIV-2002). Internally this will:
         * Remove the current Template Entity object and replace it with a 'blank' one
         * Nothing more required (this new blank object will get updated and saved as normal by other methods)
         * Record where the template came from (new)
         */
    };

    Service.prototype.duplicateTemplate = function()
    {
        /*
         * TODO (CGIV-2002). Internally this will:
         * Tell the current Template Entity to clear its ID and prepend 'DUPLICATE - ' to its name
         * Nothing more required (the object will get updated and saved as normal by other methods)
         * Record where the template came from (duplicated)
         */
    };

    Service.prototype.updateLocalTemplate = function(data)
    {
        if (!(data instanceof Object)) {
            throw 'InvalidArgumentException: InvoiceDesigner Service::updateLocalTemplate must be passed a data object';
        }

        /*
         * TODO. Internally this will:
         * Update the current Template Entity with the data provided
         */
    };

    return Service;
});