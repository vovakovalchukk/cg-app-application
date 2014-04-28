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
            var json = getMockJson();
            var template = mapper.fromJson(json);
            try {
                var templateId = template.getId();
                var templateName = template.getName();
                var elementCount = template.getElements().count();
            } catch (e) {
                var templateId = null;
                var templateName = null;
                var elementCount = 0;
            }
            expect(templateId).toBe(json.id);
            expect(templateName).toBe(json.name);
            expect(elementCount).toBe(json.elements.length);
        });

        it('should convert a template to JSON', function(mapper)
        {
            console.warn('Incomplete test: "Mapper should convert a template to JSON"');

            // Prevent change event by stubbing the trigger
            spyOn(templateEntity, 'notifyOfChange');

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
            console.warn('Incomplete test: "Mapper should convert a template to HTML"');

            var html = mapper.toHtml(templateEntity);
            expect(typeof html).toBe('string');
        });

        var getMockJson = function()
        {
            var json = {
                id: 1,
                name: "Example",
                organisationUnitId: 1,
                minHeight: 100,
                minWidth: 100,
                elements: [{
                    type: "text",
                    width: 100,
                    height: 100,
                    x: 10,
                    y: 10,
                    backgroundColour: "white",
                    borderWidth: 0,
                    borderColour: null,
                    fontSize: 12,
                    fontFamily: "Ariel",
                    fontColour: "black",
                    text: "Example text element",
                    padding: 3,
                    lineHieght: 1,
                    align: "left",
                    replacedText: "",
                    removeBlankLines: false
                }, {
                    type: "image",
                    width: 50,
                    height: 50,
                    x: 120,
                    y: 10,
                    backgroundColour: "white",
                    borderWidth: 0,
                    borderColour: null,
                    source: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==",
                    format: "png"
                }]
            };
            return json;
        }
    });
});