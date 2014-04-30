define(['jasq'], function ()
{
    describe('The Entity module', 'InvoiceDesigner/Template/Entity', function ()
    {
        it('should be an object', function(entity)
        {
            expect(typeof entity).toBe('object');
        });

        it('should not notify of changes while populating', {
            mock: {
                'InvoiceDesigner/Template/Service': {
                    notifyOfChange: function() {}
                }
            }, expect: function(entity, dependencies)
            {
                var mockService = dependencies['InvoiceDesigner/Template/Service'];
                spyOn(mockService, 'notifyOfChange');

                var data = {
                    id: 1,
                    type: "test",
                    name: "test",
                    organisationUnitId: 1,
                    minHeight: 100,
                    minWidth: 100
                };
                var populating = true;

                entity.hydrate(data, populating);
                expect(mockService.notifyOfChange).not.toHaveBeenCalled();
            }
        });

        it('should notify of changes when not populating', {
            mock: {
                'InvoiceDesigner/Template/Service': {
                    notifyOfChange: function() {}
                }
            }, expect: function(entity, dependencies)
            {
                var mockService = dependencies['InvoiceDesigner/Template/Service'];
                spyOn(mockService, 'notifyOfChange');

                entity.setId(1);
                expect(mockService.notifyOfChange).toHaveBeenCalled();
            }
        });
    });
});