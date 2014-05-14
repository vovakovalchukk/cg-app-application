define([
    'InvoiceDesigner/Template/Element/Collection',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    collection,
    domManipulator
) {
    var Entity = function()
    {
        var elements = collection;
        var manipulator = domManipulator;
        var state;
        var stateId;

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

        this.getManipulator = function()
        {
            return manipulator;
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

        this.getState = function()
        {
            return state;
        };

        this.setState = function(newState)
        {
            state = newState;
            return this;
        };

        this.getStateId = function()
        {
            return stateId;
        };

        this.setStateId = function(newStateId)
        {
            stateId = newStateId;
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
            this.getDomManipulator().notifyOfChange(this);
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