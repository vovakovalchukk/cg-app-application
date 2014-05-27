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
                },
                'InvoiceDesigner/Template/Inspector/Border': {
                    init: function() {},
                    getInspectedAttributes: function() { return ['borderWidth', 'borderColour']; },
                    getId: function() { return 'test-inspector2' },
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
                var mockBorderInspector = dependencies['InvoiceDesigner/Template/Inspector/Border'];
                spyOn(mockTextInspector, 'init');
                spyOn(mockBorderInspector, 'init');

                service.init();
                expect(mockTextInspector.init).toHaveBeenCalled();
                expect(mockBorderInspector.init).toHaveBeenCalled();
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
                var mockBorderInspector = dependencies['InvoiceDesigner/Template/Inspector/Border'];

                service.init();

                var mockTextInspectedAttributes = mockTextInspector.getInspectedAttributes();
                var inspectors = service.getInspectors();
                for (var key in mockTextInspectedAttributes) {
                    var attributeCollection = inspectors[mockTextInspectedAttributes[key]];
                    expect(attributeCollection.containsId(mockTextInspector.getId())).toBe(true);
                }
                var mockBorderInspectedAttributes = mockBorderInspector.getInspectedAttributes();
                for (var key in mockBorderInspectedAttributes) {
                    var attributeCollection = inspectors[mockBorderInspectedAttributes[key]];
                    expect(attributeCollection.containsId(mockTextInspector.getId())).toBe(false);
                }
            });

            it('should tell the inspectors to hide', function(service, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                var mockBorderInspector = dependencies['InvoiceDesigner/Template/Inspector/Border'];
                spyOn(mockTextInspector, 'hide');
                spyOn(mockBorderInspector, 'hide');

                service.init();
                service.hideAll();
                expect(mockTextInspector.hide).toHaveBeenCalled();
                expect(mockBorderInspector.hide).toHaveBeenCalled();
            });

            it('should tell the inspectors to show for an element', function(service, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                var mockBorderInspector = dependencies['InvoiceDesigner/Template/Inspector/Border'];
                spyOn(mockTextInspector, 'showForElement');
                spyOn(mockBorderInspector, 'showForElement');
                var mockElement = {
                    getInspectableAttributes: function() { return ['text']; }
                };

                service.init();
                service.showForElement(mockElement);
                expect(mockTextInspector.showForElement).toHaveBeenCalled();
                expect(mockBorderInspector.showForElement).not.toHaveBeenCalled();
            });
        }
    });
});