define(['jasq'], function ()
{
    describe('The Application module', {
        moduleName: 'InvoiceDesigner/Application',
        mock: function ()
        {
            return {
                'InvoiceDesigner/Module/TemplateSelector': {
                    init: function() {}
                }
            };
        },
        specify: function ()
        {
            it('should be an object', function(application)
            {
                expect(typeof application).toBe('object');
            });

            it('should initialise the modules', function(application, dependencies)
            {
                var mockTemplateSelector = dependencies['InvoiceDesigner/Module/TemplateSelector'];
                spyOn(mockTemplateSelector, 'init');

                application.init();
                expect(mockTemplateSelector.init).toHaveBeenCalled();
            });
        }
    });
});