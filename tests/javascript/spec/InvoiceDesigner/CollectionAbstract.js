define(['jasq'], function ()
{
    describe('The Collection module', 'InvoiceDesigner/CollectionAbstract', function ()
    {
        var item;

        beforeEach(function() {
            item = jasmine.createSpyObj('item', ['getId']);
            item.getId.andReturn(1);
        });

        it('should be able to attach items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            
            collection.attach(item);
            expect(collection.getItems()[item.getId()]).toBeDefined();
        });

        it('should not attach invalid items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var item = {};

            expect(function() { collection.attach(item); }).toThrow();
        });

        it('should be able to detach items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();

            collection.attach(item);
            expect(collection.getItems()[item.getId()]).toBeDefined();
            collection.detach(item);
            expect(collection.getItems()[item.getId()]).toBeUndefined();
        });

        it('should be able to count its items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();

            expect(collection.count()).toBe(0);
            collection.attach(item);
            expect(collection.count()).toBe(1);
        });

        it('should be able to iterate over its items', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();
            var callback = jasmine.createSpy('callback');

            collection.attach(item);
            collection.each(callback);
            expect(callback).toHaveBeenCalled();
        });

        it('should be able tell us if it contains a specified item', function(CollectionAbstract)
        {
            var collection = new CollectionAbstract();

            expect(collection.containsId(item.getId())).toBe(false);
            collection.attach(item);
            expect(collection.containsId(item.getId())).toBe(true);
            collection.detach(item);
            expect(collection.containsId(item.getId())).toBe(false);
        });

        it('should be able to merge in another collection', function(CollectionAbstract)
        {
            var collection1 = new CollectionAbstract();
            var collection2 = new CollectionAbstract();

            var item2 = jasmine.createSpyObj('item', ['getId']);
            item2.getId.andReturn(2);
            var item3 = jasmine.createSpyObj('item', ['getId']);
            item3.getId.andReturn(3);

            collection1.attach(item).attach(item2);
            collection2.attach(item).attach(item3);
            collection1.merge(collection2);
            expect(collection1.count()).toBe(3);
            expect(collection1.containsId(1)).toBe(true);
            expect(collection1.containsId(2)).toBe(true);
            expect(collection1.containsId(3)).toBe(true);
        });
    });
});