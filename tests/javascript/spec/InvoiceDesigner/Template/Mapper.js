define(['jasq', 'InvoiceDesigner/Template/Entity'], function (jasq, templateEntity)
{
    describe('The Template Mapper module', {
        moduleName: 'InvoiceDesigner/Template/Mapper',
        mock: function()
        {
            return {
                'InvoiceDesigner/Template/Service': {
                    notifyOfChange: function() {}
                }
            };
        },
        specify: function ()
        {
            var json;

            beforeEach(function()
            {
                json = {
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
            });

            it('should be an object', function(mapper)
            {
                expect(typeof mapper).toBe('object');
            });

            it('should convert JSON to a template', function(mapper)
            {
                var template = mapper.fromJson(json);

                expect(function()
                {
                    expect(template.getId()).toBe(json.id);
                    expect(template.getName()).toBe(json.name);
                    expect(template.getElements().count()).toBe(json.elements.length);
                }).not.toThrow();
            });

            it('should convert a template to JSON', function(mapper)
            {
                var template = mapper.fromJson(json);
                var mappedJson = mapper.toJson(template);
                // No easy way to compare two objects...
                for (var key in json) {
                    if (typeof json[key] === 'function' || key === 'elements') {
                        continue;
                    }
                    expect(mappedJson[key]).toBe(json[key]);
                }
                expect(mappedJson.elements.length).toBe(json.elements.length);
            });

            it('should convert a template to HTML', function(mapper)
            {
                console.warn('Incomplete test: "Mapper should convert a template to HTML"');

                var html = mapper.toHtml(templateEntity);
                expect(typeof html).toBe('string');
            });
        }
    });
});