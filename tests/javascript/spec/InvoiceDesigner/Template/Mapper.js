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
            var json = getMockJson();
            var template = mapper.fromJson(json);
            var mappedJson = mapper.toJson(template);
            // No easy way to compare two objects...
            var match = true;
            for (var key in json) {
                if (typeof json[key] === 'function' || key === 'elements') {
                    continue;
                }
                if (mappedJson[key] !== json[key]) {
                    match = false;
                    console.info('Mapper should convert a template to JSON: ' + key + ' does not match');
                    break;
                }
            }
            expect(match).toBe(true);
            expect(mappedJson.elements.length).toBe(json.elements.length);
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
                type: "invoice",
                name: "Example",
                organisationUnitId: 1,
                minHeight: 100,
                minWidth: 100,
                elements: [{
                    type: "text",
                    height: 100,
                    width: 100,
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
                    lineHeight: 1,
                    align: "left",
                    replacedText: "",
                    removeBlankLines: false
                }, {
                    type: "image",
                    height: 50,
                    width: 50,
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