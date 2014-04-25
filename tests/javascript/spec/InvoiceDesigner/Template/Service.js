define(['jasq', 'InvoiceDesigner/Template/Entity'], function (Jasq, templateEntity)
{
    describe('The Service module', 'InvoiceDesigner/Template/Service', function ()
    {
        it('should be an object', function(service)
        {
            expect(typeof service).toBe('object');
        });

        it('should be able to fetch templates by their id', function(service)
        {
            var templateId = 1;
            var template = service.fetch(templateId);
            expect(typeof template).toBe('object');
            try {
                var loadedId = template.getId();
            } catch(e) {
                var loadedId = false;
            }
            expect(loadedId).toBe(templateId);
        });

        it('should be able to save a template', function(service)
        {
            spyOn(service.getStorage(), 'save');

            service.save(templateEntity);
            expect(service.getStorage().save).toHaveBeenCalled();
        });

        it('should be able to create a new template', function(service)
        {
            var template = service.create();

            expect(typeof template).toBe('object');
            try {
                var newId = template.getId();
            } catch(e) {
                var newId = false;
            }
            expect(newId).toBe(null);
        });

        it('should be able to duplicate a template', function(service)
        {
            var template = service.duplicate(templateEntity);

            expect(typeof template).toBe('object');
            try {
                var newId = template.getId();
                var newName = template.getName();
                var oldName = templateEntity.getName();
            } catch(e) {
                var newId = false;
            }
            expect(newId).toBe(null);
            expect(newName).toBeTruthy();
            expect(newName).not.toBe(oldName);
        });
    });
});