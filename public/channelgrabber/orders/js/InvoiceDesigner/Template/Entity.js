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
            this.set('id', newId);
            return this;
        };

        this.getName = function()
        {
            return name;
        };

        this.setName = function(newName)
        {
            this.set('name', newName);
            return this;
        };

        this.getType = function()
        {
            return type;
        };

        this.setType = function(newType)
        {
            this.set('type', newType);
            return this;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            this.set('organisationUnitId', newOrganisationUnitId);
            return this;
        };

        this.getMinHeight = function()
        {
            return minHeight;
        };

        this.setMinHeight = function(newMinHeight)
        {
            this.set('minHeight', newMinHeight);
            return this;
        };

        this.getMinWidth = function()
        {
            return minWidth;
        };

        this.setMinWidth = function(newMinWidth)
        {
            this.set('minWidth', newMinWidth);
            return this;
        };

        this.set = function(field, value, populating)
        {
            value = this.formatValueForSetting(value);
            // If you are of a nervous disposition look away now
            eval(field+' = '+value);
            
            if (populating) {
                return;
            }
            this.notifyOfChange();
        };

        this.formatValueForSetting = function(value)
        {
            if (typeof value !== 'string') {
                return value;
            }
            return (value.match(/^[0-9\-\.]+$/) === null ? "'"+value+"'" : value);
        };

        this.notifyOfChange = function()
        {
            this.getService().notifyOfChange(this);
        };
    };

    Entity.prototype.hydrate = function(data, populating)
    {
        for (var field in data)
        {
            if (field === 'elements') {
                continue;
            }
            this.set(field, data[field], populating);
        }
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