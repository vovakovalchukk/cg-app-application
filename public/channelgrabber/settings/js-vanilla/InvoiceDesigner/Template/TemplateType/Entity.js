define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract',
], function(
    templateService,
    EntityHydrateAbstract,
    PubSubAbstract
) {
    let Entity = function() {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

        this.getData = function(){
            return data;
        };

        this.render = function(template) {
            let data = this.getData();
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

            this.publish();
        };
    };

    let combinedPrototype = createPrototype();

    Entity.prototype = Object.create(combinedPrototype);

    Entity.prototype.toJson = function(){
        let data = Object.assign({}, this.getData());
        let json = JSON.parse(JSON.stringify(data));
        return json;
    };

    return Entity;

    function createPrototype() {
        let combinedPrototype = EntityHydrateAbstract.prototype;
        for (var key in PubSubAbstract.prototype) {
            combinedPrototype[key] = PubSubAbstract.prototype[key];
        }
        return combinedPrototype;
    }
});