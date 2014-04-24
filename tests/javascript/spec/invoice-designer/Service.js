define(['jasq', 'invoice-designer/template/Entity'], function (Jasq, TemplateEntity)
{
    describe('The Service module', 'invoice-designer/Service', function ()
    {
        it('should be gettable via the factory method', function(Service)
        {
            var service = Service.get();
            expect(service instanceof Service).toBe(true);
        });

        it('should be able to load templates by their id', function(Service)
        {
            var service = Service.get();
            var templateId = 1;
            var template = service.loadTemplate(templateId);
            expect(template instanceof TemplateEntity).toBe(true);
            try {
                var loadedId = template.getId();
            } catch(e) {
                var loadedId = null;
            }
            expect(loadedId).toBe(templateId);
        });
    });
});