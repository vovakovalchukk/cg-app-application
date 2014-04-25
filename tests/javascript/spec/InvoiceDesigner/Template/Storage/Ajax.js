define(['jasq'], function ()
{
    describe('The Template Storage Ajax module', 'InvoiceDesigner/Template/Storage/Ajax', function ()
    {
        it('should be an object', function(storage)
        {
            expect(typeof storage).toBe('object');
        });

        it('should fetch a template by id', {
            mock: {
                jQuery: {
                    ajax: function(config) {
                        config.success({
                            id: 1
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
            }, expect: function(storage, dependencies)
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

        // TODO: test save()
    });
});