define(['./template/storage/Ajax'], function(ajaxStorage)
{
    var Service = function()
    {
        var storage = ajaxStorage;
        var template = null;
        var selectedElement = null;

        this.getStorage = function()
        {
            return storage;
        };

        this.setStorage = function(newStorage)
        {
            storage = newStorage;
            return this;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
            return this;
        };

        this.getSelectedElement = function()
        {
            return selectedElement;
        };

        this.setSelectedElement = function(newSelectedElement)
        {
            selectedElement = newSelectedElement;
            return this;
        };
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
         * Return the Template Entity
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
         * Return the Template Entity
         */
    };

    Service.prototype.duplicateTemplate = function()
    {
        /*
         * TODO (CGIV-2002). Internally this will:
         * Tell the current Template Entity to clear its ID and prepend 'DUPLICATE - ' to its name
         * Nothing more required (the object will get updated and saved as normal by other methods)
         * Record where the template came from (duplicated)
         * Return the Template Entity
         */
    };

    Service.prototype.updateLocalTemplate = function(data)
    {
        if (!(data instanceof Object)) {
            throw 'InvalidArgumentException: InvoiceDesigner Service::updateLocalTemplate must be passed a data object';
        }

        /*
         * TODO (CGIV-2009). Internally this will:
         * Update the current Template Entity with the data provided
         * Return the Template Entity
         */
    };

    Service.prototype.templateToPdf = function()
    {
        /*
         * TODO (CGIV-2009). Internally this will:
         * Convert the current Template Entity to its JSON representation (via a mapper)
         * Make an AJAX call to render a PDF version of it (this should probably return a url which then gets opened in a new window)
         */
    };

    Service.prototype.addElement = function(element)
    {
        /*
         * TODO (CGIV-2009). Internally this will:
         * Pass the element onto the current Template Entity
         */
    };

    Service.prototype.removeElement = function(element)
    {
        /*
         * TODO (CGIV-2009). Internally this will:
         * Pass the element onto the current Template Entity to remove
         */
    };

    return new Service();
});