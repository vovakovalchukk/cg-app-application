define(['jasq', 'invoice-designer/template/Entity'], function (Jasq, templateEntity)
{
    describe('The Service module', 'invoice-designer/Service', function ()
    {
        it('should be an object', function(service)
        {
            expect(typeof service).toBe('object');
        });

        it('should be able to load templates by their id', function(service)
        {
            var templateId = 1;
            var template = service.loadTemplate(templateId);
            expect(typeof template).toBe('object');
            try {
                var loadedId = template.getId();
            } catch(e) {
                var loadedId = false;
            }
            expect(loadedId).toBe(templateId);
        });

        it('should be able to save the current template', function(service)
        {
            service.setTemplate(templateEntity);

            spyOn(service.getStorage(), 'save');

            service.saveCurrentTemplate();
            expect(service.getStorage().save).toHaveBeenCalled();
        });

        it('should be able to create a new template', function(service)
        {
            var template = service.newTemplate();

            expect(typeof template).toBe('object');
            try {
                var newId = template.getId();
            } catch(e) {
                var newId = false;
            }
            expect(newId).toBe(null);
        });

        it('should be able to duplicate the current template', function(service)
        {
            service.setTemplate(templateEntity);
            var template = service.duplicateTemplate();

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

        it('should be able to update the current template', function(service)
        {
            var data = {name: '__TEST_NAME__'};
            service.setTemplate(templateEntity);
            service.updateLocalTemplate(data);

            expect(templateEntity.getName()).toBe(data.name);
        });

        it('should be able to add an element to the current template', function(service)
        {
            var element = {};
            service.setTemplate(templateEntity);

            spyOn(templateEntity, 'addElement');

            service.addElement(element);
            expect(templateEntity.addElement).toHaveBeenCalledWith(element);
        });

        it('should be able to remove an element from the current template', function(service)
        {
            var element = {};
            service.setTemplate(templateEntity);

            spyOn(templateEntity, 'removeElement');

            service.removeElement(element);
            expect(templateEntity.removeElement).toHaveBeenCalledWith(element);
        });
    });
});