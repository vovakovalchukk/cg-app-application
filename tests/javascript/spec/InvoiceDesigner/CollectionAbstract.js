define(['jasq'], function ()
{
    describe('The Collection module', 'InvoiceDesigner/CollectionAbstract', function ()
    {
        it('should be able to attach items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var item = getMockItem();
            
            collection.attach(item);
            expect(collection.getItems()[item.getId()]).toBeDefined();
        });

        it('should not attach invalid items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var item = {};

            try {
                collection.attach(item);
                var errored = false;
            } catch (e) {
                var errored = true;
            }
            expect(errored).toBe(true);
        });

        it('should be able to detach items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var item = getMockItem();

            collection.attach(item);
            expect(collection.getItems()[item.getId()]).toBeDefined();
            collection.detach(item);
            expect(collection.getItems()[item.getId()]).toBeUndefined();
        });

        it('should be able to count its items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var item = getMockItem();

            expect(collection.count()).toBe(0);
            collection.attach(item);
            expect(collection.count()).toBe(1);
        });

        it('should be able to iterate over its items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var item = getMockItem();
            var callback = jasmine.createSpy('callback');

            collection.attach(item);
            collection.each(callback);
            expect(callback).toHaveBeenCalled();
        });

        var getMockItem = function()
        {
            var item = {
                getId: function()
                {
                    return 1;
                }
            };
            return item;
        };
    });
});