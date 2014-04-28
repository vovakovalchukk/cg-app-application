define(['jasq', 'InvoiceDesigner/Template/Entity'], function (jasq, templateEntity)
{
    describe('The Template Mapper module', 'InvoiceDesigner/Template/Mapper', function ()
    {
        it('should be an object', function(mapper)
        {
            expect(typeof mapper).toBe('object');
        });

        it('should convert JSON to a template', function(mapper)
        {
            var json = {
                id: 1,
                name: 'Test'
            };
            var template = mapper.fromJson(json);
            try {
                expect(template.getId()).toBe(json.id);
                expect(template.getName()).toBe(json.name);
            } catch (e) {
                expect(null).toBe(json.id);
                expect(null).toBe(json.name);
            }
        });

        it('should convert a template to JSON', function(mapper)
        {
            templateEntity.setId(1);
            var json = mapper.toJson(templateEntity);
            expect(typeof json).toBe('object');
            try {
                expect(json.id).toBe(templateEntity.getId());
            } catch (e) {
                expect(null).toBe(templateEntity.getId());
            }
        });

        it('should convert a template to HTML', function(mapper)
        {
            var html = mapper.toHtml(templateEntity);
            expect(typeof html).toBe('string');
        });
    });
});