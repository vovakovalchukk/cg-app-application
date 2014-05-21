define(['jasq', 'InvoiceDesigner/Template/Entity'], function (jasq, TemplateEntity)
{
    describe('The Template Storage Ajax module', 'InvoiceDesigner/Template/Storage/Ajax', function ()
    {
        var templateEntity;
        beforeEach(function()
        {
            templateEntity = new TemplateEntity();
        });

        it('should be an object', function(storage)
        {
            expect(typeof storage).toBe('object');
        });

        it('should fetch a template by id', {
            mock: {
                jquery: {
                    ajax: function(config) {
                        config.success({
                            template: "{ \"id\": 1, \"type\": \"Invoice\" }"
                        });
                    }
                },
                'InvoiceDesigner/Template/Mapper': {
                    fromJson: function(json) {
                        return {
                            getId: function()
                            {
                                return json.id;
                            }
                        };
                    }
                }
            }, expect: function(storage)
            {
                var id = 1;
                var template = storage.fetch(id);
                expect(typeof template).toBe('object');
                try {
                    var templateId = template.getId();
                } catch (e) {
                    var templateId = null;
                }
                expect(templateId).toBe(id);
            }
        });

        it('should save a template', {
            mock: {
                jquery: {
                    ajax: function(config) {
                        config.success({});
                    }
                },
                'InvoiceDesigner/Template/Mapper': {
                    toJson: function(template) {
                        return {};
                    }
                }
            }, expect: function(storage, dependencies)
            {
                spyOn(dependencies.jquery, 'ajax');
                storage.save(templateEntity);
                expect(dependencies.jquery.ajax).toHaveBeenCalled();
            }
        });
    });
});