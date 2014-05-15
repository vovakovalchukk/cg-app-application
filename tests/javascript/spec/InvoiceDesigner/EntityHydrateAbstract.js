define(['jasq'], function ()
{
    describe('The EntityHydrate module', 'InvoiceDesigner/EntityHydrateAbstract', function ()
    {
        var setFunction;
        var data;

        beforeEach(function() {
            setFunction = jasmine.createSpy('set');
            data = {
                width: 10,
                height: 10
            };
        });

        it('should hydrate the entity', function(EntityHydrateAbstract)
        {
            var element = new EntityHydrateAbstract();
            element.set = setFunction;

            var populating = true;
            element.hydrate(data, populating);
            expect(element.set.calls.length).toBe(2);
            expect(element.set).toHaveBeenCalledWith('width', data.width, populating);
            expect(element.set).toHaveBeenCalledWith('height', data.height, populating);
        });

        it('should not hydrate fields that are blocked', function(EntityHydrateAbstract)
        {
            var element = new EntityHydrateAbstract();
            element.set = setFunction;
            element.shouldFieldBeHydrated = function(field)
            {
                if (field === 'height') {
                    return false;
                }
                return true;
            };

            var populating = true;
            element.hydrate(data, populating);
            expect(element.set.calls.length).toBe(1);
            expect(element.set).toHaveBeenCalledWith('width', data.width, populating);
            expect(element.set).not.toHaveBeenCalledWith('height', data.height, populating);
        });
    });
});