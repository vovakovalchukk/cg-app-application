define(['./Element/Collection', './Service'], function(collection, templateService)
{
    var Entity = function()
    {
        var elements = collection;
        var service = templateService;

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

        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
            this.triggerChangeEvent();
            return this;
        };

        this.getName = function()
        {
            return name;
        };

        this.setName = function(newName)
        {
            name = newName;
            this.triggerChangeEvent();
            return this;
        };

        this.getType = function()
        {
            return type;
        };

        this.setType = function(newType)
        {
            type = newType;
            this.triggerChangeEvent();
            return this;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            organisationUnitId = newOrganisationUnitId;
            this.triggerChangeEvent();
            return this;
        };

        this.getMinHeight = function()
        {
            return minHeight;
        };

        this.setMinHeight = function(newMinHeight)
        {
            minHeight = newMinHeight;
            this.triggerChangeEvent();
            return this;
        };

        this.getMinWidth = function()
        {
            return minWidth;
        };

        this.setMinWidth = function(newMinWidth)
        {
            minWidth = newMinWidth;
            this.triggerChangeEvent();
            return this;
        };

        this.triggerChangeEvent = function()
        {
            this.getService().triggerTemplateChangeEvent(this);
        };
    };

    Entity.prototype.addElement = function(element)
    {
        this.getElements().attach(element);
        element.subscribe(this);
        this.triggerChangeEvent();
        return this;
    };

    Entity.prototype.removeElement = function(element)
    {
        this.getElements().detch(element);
        element.unsubscribe(this);
        this.triggerChangeEvent();
        return this;
    };

    Entity.prototype.elementUpdate = function(element)
    {
        this.triggerChangeEvent();
    };

    return new Entity();
});