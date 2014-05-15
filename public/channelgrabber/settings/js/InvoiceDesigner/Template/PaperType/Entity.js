define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
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
            backgroundImage: undefined,
            backgroundImageInverse: undefined
        };

        this.getService = function()
        {
            return service;
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

        this.getBackgroundImage = function()
        {
            return this.get('backgroundImage');
        };

        this.setBackgroundImage = function(newBackgroundImage)
        {
            this.set('backgroundImage', newBackgroundImage);
            return this;
        };

        this.getBackgroundImageInverse = function()
        {
            return this.get('backgroundImageInverse');
        }

        this.setBackgroundImageInverse = function(newBackgroundImageInverse)
        {
            this.set('backgroundImageInverse', newBackgroundImageInverse);
            return this;
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