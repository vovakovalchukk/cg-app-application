define(['jasq'], function ()
{
    describe('The Template Selector module', 'InvoiceDesigner/Module/TemplateSelector', function ()
    {
        it('should be an object', function(templateSelector)
        {
            expect(typeof templateSelector).toBe('object');
        });

        it('should initialise the dom listener', {
            mock: {
                'InvoiceDesigner/Module/DomListener/TemplateSelector': {
                    init: function() {}
                }
            }, expect: function(templateSelector, dependencies)
            {
                var mockDomListener = dependencies['InvoiceDesigner/Module/DomListener/TemplateSelector'];
                spyOn(mockDomListener, 'init');

                templateSelector.init();
                expect(mockDomListener.init).toHaveBeenCalled();
            }
        });

        it('should load a template when selected', {
            mock: {
                'InvoiceDesigner/Template/Service': {
                    fetch: function() {},
                    loadModules: function() {}
                }
            }, expect: function(templateSelector, dependencies)
            {
                var mockService = dependencies['InvoiceDesigner/Template/Service'];
                spyOn(mockService, 'fetch');
                spyOn(mockService, 'loadModules');

                var id = 1;
                templateSelector.selectionMade(id);
                expect(mockService.fetch).toHaveBeenCalled();
                expect(mockService.loadModules).toHaveBeenCalled();
            }
        });
    });
});