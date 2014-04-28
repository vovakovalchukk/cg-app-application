define(['./Element/Collection', './Service'], function(collection, templateService)
{
    var Entity = function()
    {
        var elements = collection;
        var service = templateService;
        var populating = false;

        var id;
        var name;
        var type;
        var organisationUnitId;
        var minHeight;
        var minWidth;

        this.getElements = function()
        {
            return elements;
        };

        this.getService = function()
        {
            return service;
        };

        this.isPopulating = function()
        {
            return populating;
        };

        this.setPopulating = function(newPopulating)
        {
            populating = newPopulating;
            return this;
        };

        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
            this.notifyOfChange();
            return this;
        };

        this.getName = function()
        {
            return name;
        };

        this.setName = function(newName)
        {
            name = newName;
            this.notifyOfChange();
            return this;
        };

        this.getType = function()
        {
            return type;
        };

        this.setType = function(newType)
        {
            type = newType;
            this.notifyOfChange();
            return this;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            organisationUnitId = newOrganisationUnitId;
            this.notifyOfChange();
            return this;
        };

        this.getMinHeight = function()
        {
            return minHeight;
        };

        this.setMinHeight = function(newMinHeight)
        {
            minHeight = newMinHeight;
            this.notifyOfChange();
            return this;
        };

        this.getMinWidth = function()
        {
            return minWidth;
        };

        this.setMinWidth = function(newMinWidth)
        {
            minWidth = newMinWidth;
            this.notifyOfChange();
            return this;
        };

        this.notifyOfChange = function()
        {
            if (this.isPopulating()) {
                return;
            }
            this.getService().notifyOfChange(this);
        };
    };

    Entity.prototype.addElement = function(element)
    {
        this.getElements().attach(element);
        element.subscribe(this);
        this.notifyOfChange();
        return this;
    };

    Entity.prototype.removeElement = function(element)
    {
        this.getElements().detch(element);
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