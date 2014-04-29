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
                    try {
                        inspectorManager.init();
                        var errored = false;
                    } catch (e) {
                        var errored = true;
                    }
                    expect(errored).toBe(true);
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
        }
    });
});