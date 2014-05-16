define(['jasq', 'InvoiceDesigner/Template/Mapper'], function (jasq, mapper)
{
    describe('The Template Service module', {
        moduleName: 'InvoiceDesigner/Template/Service',
        mock: function()
        {
            return {
                'InvoiceDesigner/Template/Storage/Ajax': {
                    fetch: function(id)
                    {
                        templateEntity.setId(id);
                        return templateEntity;
                    },
                    save: function() {}
                },
                'InvoiceDesigner/Template/DomManipulator': {
                    triggerTemplateChangeEvent: function() {},
                    insertTemplateHtml: function() {}
                }
            };
        },
        specify: function ()
        {
            var templateEntity;
            beforeEach(function()
            {
                var json = {
                    id: 1,
                    type: "invoice",
                    name: "Example",
                    organisationUnitId: 1,
                    minHeight: 100,
                    minWidth: 100,
                    elements: [{
                        type: "page",
                        width: 250,
                        height: 353
                    }]
                };
                templateEntity = mapper.fromJson(json);
            });

            it('should be an object', function(service)
            {
                expect(typeof service).toBe('object');
            });

            it('should be able to fetch templates by their id', function(service)
            {
                var templateId = 1;
                var template = service.fetch(templateId);
                expect(typeof template).toBe('object');
                expect(function()
                {
                    expect(template.getId()).toBe(templateId);
                }).not.toThrow();
            });

            it('should be able to save a template', function(service, dependencies)
            {
                var storage = dependencies['InvoiceDesigner/Template/Storage/Ajax'];
                spyOn(storage, 'save');

                service.save(templateEntity);
                expect(storage.save).toHaveBeenCalled();
            });

            it('should be able to create a new template', function(service)
            {
                var template = service.create();

                expect(typeof template).toBe('object');
                expect(function()
                {
                    expect(template.getId()).toBe(null);
                }).not.toThrow();
            });

            it('should be able to duplicate a template', function(service)
            {
                var template = service.duplicate(templateEntity);

                expect(typeof template).toBe('object');
                expect(function()
                {
                    expect(template.getId()).toBe(null);
                    expect(template.getName()).toBeTruthy();
                    expect(template.getName()).not.toBe(templateEntity.getName());
                }).not.toThrow();
            });

            it('should be able to render a template', function(service, dependencies)
            {
                var domManipulator = dependencies['InvoiceDesigner/Template/DomManipulator'];
                spyOn(domManipulator, 'insertTemplateHtml');

                service.render(templateEntity);
                expect(domManipulator.insertTemplateHtml).toHaveBeenCalled();
            });
        }
    });
});