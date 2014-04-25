define(['jasq'], function ()
{
    describe('The Application module', 'InvoiceDesigner/Application', function ()
    {
        it('should be an object', function(application)
        {
            expect(typeof application).toBe('object');
        });

        it('should initialise the modules', {
            mock: {
                'InvoiceDesigner/Module/TemplateSelector': {
                    init: function() {}
                }
            }, expect: function(application, dependencies)
            {
                var mockTemplateSelector = dependencies['InvoiceDesigner/Module/TemplateSelector'];
                spyOn(mockTemplateSelector, 'init');

                application.init();
                expect(mockTemplateSelector.init).toHaveBeenCalled();
            }
        });
    });
});