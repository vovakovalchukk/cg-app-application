define([
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/Template/Element/Collection',
    'InvoiceDesigner/Template/Service'
], function(
    EntityHydrateAbstract,
    collection,
    templateService
) {
    var Entity = function()
    {
        EntityHydrateAbstract.call(this);

        var elements = collection;
        var service = templateService;
        var page;

        // Member vars to watch for changes
        var data = {
            id: undefined,
            name: undefined,
            type: undefined,
            organisationUnitId: undefined,
            minHeight: undefined,
            minWidth: undefined
        };

        this.getElements = function()
        {
            return elements;
        };

        this.getService = function()
        {
            return service;
        };

        this.getPage = function()
        {
            return page;
        };

        this.setPage = function(newPage)
        {
            page = newPage;
            return this;
        };

        this.getId = function()
        {
            return this.get('id');
        };

        this.setId = function(newId)
        {
            this.set('id', newId);
            return this;
        };

        this.getName = function()
        {
            return this.get('name');
        };

        this.setName = function(newName)
        {
            this.set('name', newName);
            return this;
        };

        this.getType = function()
        {
            return this.get('type');
        };

        this.setType = function(newType)
        {
            this.set('type', newType);
            return this;
        };

        this.getOrganisationUnitId = function()
        {
            return this.get('organisationUnitId');
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            this.set('organisationUnitId', newOrganisationUnitId);
            return this;
        };

        this.getMinHeight = function()
        {
            return this.get('minHeight');
        };

        this.setMinHeight = function(newMinHeight)
        {
            this.set('minHeight', newMinHeight);
            return this;
        };

        this.getMinWidth = function()
        {
            return this.get('minWidth');
        };

        this.setMinWidth = function(newMinWidth)
        {
            this.set('minWidth', newMinWidth);
            return this;
        };

        this.get = function(field)
        {
            return data[field];
        };

        this.set = function(field, value, populating)
        {
            data[field] = value;
            
            if (populating) {
                return;
            }
            this.notifyOfChange();
        };

        this.notifyOfChange = function()
        {
            this.getService().notifyOfChange(this);
        };
    };

    Entity.prototype = Object.create(EntityHydrateAbstract.prototype);

    Entity.prototype.shouldFieldBeHydrated = function(field)
    {
        return (field !== 'elements');
    };

    Entity.prototype.addElement = function(element, populating)
    {
        this.getElements().attach(element);
        element.subscribe(this);
        if (element.getType() === 'page') {
            this.setPage(element);
        }
        if (populating) {
            return this;
        }
        this.notifyOfChange();
        return this;
    };

    Entity.prototype.removeElement = function(element)
    {
        this.getElements().detach(element);
        element.unsubscribe(this);
        this.notifyOfChange();
        return this;
    };

    Entity.prototype.publisherUpdate = function(element)
    {
        this.notifyOfChange();
    };

    return new Entity();
});