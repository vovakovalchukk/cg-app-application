define([
    'InvoiceDesigner/Template/Element/Collection',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/EntityHydrateAbstract',
], function(
    Collection,
    domManipulator,
    EntityHydrateAbstract
) {
    var Entity = function()
    {
        EntityHydrateAbstract.call(this);

        let elements = new Collection();
        let manipulator = domManipulator;
        let state;
        let stateId;
        let paperPage;
        let printPage;
        let multiPage;

        // Member vars to watch for changes
        var data = {
            storedETag: undefined,
            id: undefined,
            name: undefined,
            type: Entity.TYPE,
            typeId: undefined,
            organisationUnitId: undefined,
            editable: true
        };

        this.getEntityName = function() {
            return 'Template';
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

        this.getPrintPage = function()
        {
            return printPage;
        };

        this.setPrintPage = function(newPrintPage)
        {
            printPage = newPrintPage;
            printPage.subscribe(this);
            return this;
        };

        this.getMultiPage = function()
        {
            return multiPage;
        };

        this.setMultiPage = function(newMultiPage)
        {
            multiPage = newMultiPage;
            multiPage.subscribe(this);
            return this;
        };

        this.getStoredETag = function()
        {
            return this.get('storedETag');
        };

        this.setStoredETag = function(newStoredETag)
        {
            // Setting the eTag is not a user change so we never want to trigger the save bar for it
            this.set('storedETag', newStoredETag, true);
            return this;
        };

        this.getId = function()
        {
            return this.get('id');
        };

        this.setId = function(newId)
        {
            this.set('id', newId, true);
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
            this.set('type', newType, true);
            return this;
        };

        this.getTypeId = function()
        {
            return this.get('typeId');
        };

        this.setTypeId = function(newTypeId)
        {
            this.set('typeId', newTypeId, true);
            return this;
        };

        this.getOrganisationUnitId = function()
        {
            return this.get('organisationUnitId');
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            this.set('organisationUnitId', newOrganisationUnitId, true);
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
            return this.get('editable');
        };

        this.setEditable = function(newEditable)
        {
            this.set('editable', newEditable, true);
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

        this.notifyOfChange = function(topicUpdates, bypassSaveDiscardBar)
        {
            this.getDomManipulator().triggerTemplateChangeEvent(this, topicUpdates, bypassSaveDiscardBar);
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

    Entity.prototype.publisherUpdate = function(element, topicUpdates, bypassSaveDiscardBar)
    {
        this.notifyOfChange(topicUpdates, bypassSaveDiscardBar);
    };

    return Entity;
});