define(['jasq'], function ()
{
    describe('The Inspector Service module', {
        moduleName: 'InvoiceDesigner/Template/Inspector/Service',
        mock: function()
        {
            return {
                'InvoiceDesigner/Template/Inspector/TextArea': {
                    init: function() {},
                    getInspectedAttributes: function() { return ['text', 'source']; },
                    getId: function() { return 'test-inspector' },
                    hide: function() {},
                    showForElement: function() {}
                }
            };
        }, specify: function ()
        {
            it('should be an object', function(service)
            {
                expect(typeof service).toBe('object');
            });

            it('should initialise the inspectors', function(service, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                spyOn(mockTextInspector, 'init');

                service.init();
                expect(mockTextInspector.init).toHaveBeenCalled();
            });

            it('should not initialise if there are invalid inspectors', {
                mock: {
                    'InvoiceDesigner/Template/Inspector/TextArea': {}
                }, expect: function(service)
                {
                    expect(function() { service.init(); }).toThrow();
                }
            });

            it('should store the inspectors against the right attributes', function(service, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];

                service.init();

                var mockInspectedAttributes = mockTextInspector.getInspectedAttributes();
                var inspectors = service.getInspectors();
                for (var key in mockInspectedAttributes) {
                    var attributeCollection = inspectors[mockInspectedAttributes[key]];
                    expect(attributeCollection.containsId(mockTextInspector.getId())).toBe(true);
                }
            });

            it('should tell the inspectors to hide', function(service, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                spyOn(mockTextInspector, 'hide');

                service.init();
                service.hideAll();
                expect(mockTextInspector.hide).toHaveBeenCalled();
            });

            it('should tell the inspectors to show', function(service, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                spyOn(mockTextInspector, 'showForElement');
                var mockElement = {
                    getInspectableAttributes: function() { return ['text']; }
                };

                service.init();
                service.showForElement(mockElement);
                expect(mockTextInspector.showForElement).toHaveBeenCalled();
            });
        }
    });
});