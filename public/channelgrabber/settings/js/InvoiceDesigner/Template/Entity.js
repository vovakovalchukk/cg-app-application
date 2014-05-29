define([
    'InvoiceDesigner/Template/Element/Collection',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/Template/Entity'
], function(
    Collection,
    domManipulator,
    EntityHydrateAbstract
) {
    var Entity = function()
    {
        EntityHydrateAbstract.call(this);

        var elements = new Collection();
        var manipulator = domManipulator;
        var state;
        var stateId;
        var paperPage;
        var editable = true;

        // Member vars to watch for changes
        var data = {
            storedETag: undefined,
            id: undefined,
            name: undefined,
            type: Entity.TYPE,
            organisationUnitId: undefined,
            minHeight: 0,
            minWidth: 0
        };

        this.getElements = function()
        {
            return elements;
        };

        this.getManipulator = function()
        {
            return manipulator;
        };

        this.getPaperPage = function()
        {
            return paperPage;
        };

        this.setPaperPage = function(newPaperPage)
        {
            paperPage = newPaperPage;
            paperPage.subscribe(this);
            return this;
        };

        this.getStoredETag = function()
        {
            return this.get('storedETag');
        };

        this.setStoredETag = function(newStoredETag)
        {
            this.set('storedETag', newStoredETag);
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

        this.isEditable = function()
        {
            return editable;
        };

        this.setEditable = function(newEditable)
        {
            editable = newEditable;
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

        this.getDomManipulator = function()
        {
            return manipulator;
        };

        this.notifyOfChange = function()
        {
            this.getDomManipulator().triggerTemplateChangeEvent(this);
        };
    };

    Entity.TYPE = 'invoice';

    Entity.prototype = Object.create(EntityHydrateAbstract.prototype);

    Entity.prototype.shouldFieldBeHydrated = function(field)
    {
        var skip = ['elements', 'paperPage'];
        return (skip.indexOf(field) < 0);
    };

    Entity.prototype.addElement = function(element, populating)
    {
        this.getElements().attach(element);
        element.subscribe(this);
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
        this.getDomManipulator().triggerElementDeletedEvent(element);
        this.notifyOfChange();
        return this;
    };

    Entity.prototype.publisherUpdate = function(element)
    {
        this.notifyOfChange();
    };

    return Entity;
});