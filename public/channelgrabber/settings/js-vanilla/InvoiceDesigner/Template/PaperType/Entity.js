define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract'
], function(
    templateService,
    EntityHydrateAbstract
) {
    var Entity = function()
    {
        EntityHydrateAbstract.call(this);

        var service = templateService;
        var data = {
            id: undefined,
            name: undefined,
            height: undefined,
            width: undefined,
        };

        this.getService = function()
        {
            return service;
        };

        this.getEntityName = function() {
            return 'PaperType';
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

        this.setHeight = function(newHeight)
        {
            this.set('height', newHeight);
            return this;
        }

        this.getHeight = function()
        {
            return this.get('height');
        }

        this.setWidth = function(newWidth)
        {
            this.set('width', newWidth);
            return this;
        }

        this.getWidth = function()
        {
            return this.get('width');
        }

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

    return Entity;
});