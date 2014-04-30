define(['jasq'], function ()
{
    describe('The InspectorManager module', {
        moduleName: 'InvoiceDesigner/Template/InspectorManager',
        mock: function()
        {
            return {
                'InvoiceDesigner/Template/Inspector/TextArea': {
                    init: function() {},
                    getSupportedTypes: function() { return ['text', 'image']; },
                    getId: function() { return 'test-inspector' },
                    clear: function() {},
                    showForElement: function() {}
                }
            };
        }, specify: function ()
        {
            it('should be an object', function(inspectorManager)
            {
                expect(typeof inspectorManager).toBe('object');
            });

            it('should initialise the inspectors', function(inspectorManager, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                spyOn(mockTextInspector, 'init');

                inspectorManager.init();
                expect(mockTextInspector.init).toHaveBeenCalled();
            });

            it('should not initialise if there are invalid inspectors', {
                mock: {
                    'InvoiceDesigner/Template/Inspector/TextArea': {}
                }, expect: function(inspectorManager)
                {
                    expect(function() { inspectorManager.init(); }).toThrow();
                }
            });

            it('should store the inspectors against the right types', function(inspectorManager, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];

                inspectorManager.init();

                var mockSupportedTypes = mockTextInspector.getSupportedTypes();
                for (var key in mockSupportedTypes) {
                    var typeCollection = inspectorManager.getForType(mockSupportedTypes[key]);
                    expect(typeCollection.containsId(mockTextInspector.getId())).toBe(true);
                }
            });

            it('should tell the inspectors to clear', function(inspectorManager, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                spyOn(mockTextInspector, 'clear');

                inspectorManager.init();
                inspectorManager.clear();
                expect(mockTextInspector.clear).toHaveBeenCalled();
            });

            it('should tell the inspectors to show', function(inspectorManager, dependencies)
            {
                var mockTextInspector = dependencies['InvoiceDesigner/Template/Inspector/TextArea'];
                spyOn(mockTextInspector, 'showForElement');
                var mockElement = {
                    getType: function() { return 'text'; }
                };

                inspectorManager.init();
                inspectorManager.showForElement(mockElement);
                expect(mockTextInspector.showForElement).toHaveBeenCalled();
            });
        }
    });
});