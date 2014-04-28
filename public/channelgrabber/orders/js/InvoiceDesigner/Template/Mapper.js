define([
    'require',
    './Entity',
    './Element/Box',
    './Element/DeliveryAddress',
    './Element/Image',
    './Element/OrderTable',
    './Element/Paper',
    './Element/SellerAddress',
    './Element/Text'
], function(require)
{
    var Mapper = function()
    {

    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Mapper::fromJson must be passed a JSON object';
        }

        var template = require('./Entity');
        template.setPopulating(true)
            .setId(json.id)
            .setName(json.name)
            .setType(json.type)
            .setOrganisationUnitId(json.organisationUnitId)
            .setMinHeight(json.minHeight)
            .setMinWidth(json.minWidth);

        for (var key in json.elements) {
            var elementData = json.elements[key];
            var element = this.elementFromJson(elementData);
            template.addElement(element);
        }

        template.setPopulating(false);
        return template;
    };

    Mapper.prototype.elementFromJson = function(elementData)
    {
        var elementType = elementData.type.charAt(0).toUpperCase() + elementData.type.substr(1);
        var element = require('./Element/' + elementType);
        for (var field in elementData) {
            var setter = 'set' + field.charAt(0).toUpperCase() + field.substr(1);
            if (element[setter]) {
                element[setter](elementData[field]);
            }
        }
        return element;
    };

    Mapper.prototype.toJson = function(template)
    {
        var json = {
            id: template.getId(),
            type: template.getType(),
            name: template.getName(),
            organisationUnitId: template.getOrganisationUnitId(),
            minHeight: template.getMinHeight(),
            minWidth: template.getMinWidth(),
            elements: []
        };

        template.getElements().each(function(element)
        {
            json.elements.push(element.toJson());
        });

        return json;
    };

    Mapper.prototype.toHtml = function(template)
    {
        /*
         * TODO (CGIV-2009)
         */
    };

    return new Mapper();
});