define(['jasq'], function ()
{
    describe('The Entity module', {
        moduleName: 'InvoiceDesigner/Template/Entity',
        mock: function()
        {
            return {
                'InvoiceDesigner/Template/DomManipulator': {
                    triggerTemplateChangeEvent: function() {}
                }
            };
        },
        specify: function ()
        {
            it('should be a class', function(Entity)
            {
                expect(typeof Entity).toBe('function');
            });

            it('should not notify of changes while populating', function(Entity, dependencies)
            {
                var mockDomManipulator = dependencies['InvoiceDesigner/Template/DomManipulator'];
                spyOn(mockDomManipulator, 'triggerTemplateChangeEvent');

                var data = {
                    id: 1,
                    type: "test",
                    name: "test",
                    organisationUnitId: 1,
                    minHeight: 100,
                    minWidth: 100
                };
                var populating = true;

                var entity = new Entity();
                entity.hydrate(data, populating);
                expect(mockDomManipulator.triggerTemplateChangeEvent).not.toHaveBeenCalled();
            });

            it('should notify of changes when not populating', function(Entity, dependencies)
            {
                var mockDomManipulator = dependencies['InvoiceDesigner/Template/DomManipulator'];
                spyOn(mockDomManipulator, 'triggerTemplateChangeEvent');

                var entity = new Entity();
                entity.setId(1);
                expect(mockDomManipulator.triggerTemplateChangeEvent).toHaveBeenCalled();
            });
        }
    });
});