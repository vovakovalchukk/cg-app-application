define(['cg-mustache'], function(CGMustache)
{
    var MapperAbstract = function()
    {
        var optionalAttribs = ['x', 'y', 'backgroundColour', 'borderWidth', 'borderColour'];
        this.getOptionalAttribs = function()
        {
            return optionalAttribs;
        };

        var cgMustache = new CGMustache();
        this.getCGMustache = function()
        {
            return cgMustache;
        };
    };

    MapperAbstract.ELEMENT_DOM_ID_PREFIX = 'template-element-';
    MapperAbstract.ELEMENT_TEMPLATE_PATH = '/channelgrabber/settings/template/InvoiceDesigner/Template/Element/';

    MapperAbstract.getDomId = function(element)
    {
        return MapperAbstract.ELEMENT_DOM_ID_PREFIX+element.getId();
    };

    MapperAbstract.getElementIdFromDomId = function(domId)
    {
        return domId.replace(MapperAbstract.ELEMENT_DOM_ID_PREFIX, '');
    };

    MapperAbstract.attributePropertyMap = {
        x: "left",
        y: "top",
        padding: "padding-left"
    };

    MapperAbstract.attributePropertyAdditionalMap = {
        borderWidth: ['border-style: solid']
    };

    MapperAbstract.prototype.toHtml = function(element)
    {
        var domId = MapperAbstract.getDomId(element);
        var cssClasses = this.getDomClasses(element).join(' ');
        var cssStyle = this.getDomStyles(element).join('; ');
        var htmlContents = this.getHtmlContents(element);

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH+'abstract.mustache';
        var data = {
            id: domId,
            classes: cssClasses,
            styles: cssStyle,
            contents: htmlContents
        };
        var html = this.renderMustacheTemplate(templateUrl, data);

        return html;
    };

    MapperAbstract.prototype.renderMustacheTemplate = function(templateUrl, data)
    {
        var html;
        this.getCGMustache().fetchTemplate(templateUrl, function(template, cgMustache)
        {
            html = cgMustache.renderTemplate(template, data);
        });
        return html;
    };

    MapperAbstract.prototype.getDomClasses = function(element)
    {
        var domClasses = ['template-element'];
        if (element.getType()) {
            domClasses.push('template-element-' + element.getType().toLowerCase());
        }
        var extraDomClasses = this.getExtraDomClasses(element);
        for (var key in extraDomClasses) {
            domClasses.push(extraDomClasses[key]);
        }
        return domClasses;
    };

    /**
     * Sub-classes can override this to provide extra css classes for the DOM container
     */
    MapperAbstract.prototype.getExtraDomClasses = function(element)
    {
        return [];
    };

    MapperAbstract.prototype.getDomStyles = function(element)
    {
        var domStyles = [
            'width: '+element.getWidth()+'mm',
            'height: '+element.getHeight()+'mm'
        ];
        var optionalAttribs = this.getOptionalAttribs();
        domStyles = this.addOptionalDomStyles(element, optionalAttribs, domStyles);
        var extraDomStyles = this.getExtraDomStyles(element);
        for (var key in extraDomStyles) {
            domStyles.push(extraDomStyles[key]);
        }
        return domStyles;
    };

    MapperAbstract.prototype.addOptionalDomStyles = function(element, optionalAttribs, domStyles)
    {
        for (var key in optionalAttribs) {
            var attribute = optionalAttribs[key];
            var property = this.elementAttributeToCssProperty(attribute);
            var getter = 'get' + attribute.ucfirst();
            if (!element[getter]()) {
                continue;
            }
            var value = this.elementAttributeValueToCssPropertyValue(element[getter]());
            domStyles.push(property+': '+value);
            var additionalStyles = this.getAdditionalStylesForAttribute(attribute, value);
            for (var key2 in additionalStyles) {
                domStyles.push(additionalStyles[key2]);
            }
        };
        return domStyles;
    };

    MapperAbstract.prototype.elementAttributeToCssProperty = function(attribute)
    {
        var map = MapperAbstract.attributePropertyMap;
        var extraMap = this.getExtraAttributePropertyMap();
        for (var key in extraMap) {
            map[key] = extraMap[key];
        }
        if (map[attribute]) {
            return map[attribute];
        }
        return attribute.camelCaseToDashed().replace('colour', 'color');
    };

    MapperAbstract.prototype.elementAttributeValueToCssPropertyValue = function(value)
    {
        if (value !== '' && !isNaN(Number(value))) {
            return value+'mm';
        }
        return value;
    };

    MapperAbstract.prototype.getAdditionalStylesForAttribute = function(attribute, value)
    {
        if (!MapperAbstract.attributePropertyAdditionalMap[attribute]) {
            return [];
        }

        if (typeof MapperAbstract.attributePropertyAdditionalMap[attribute] === 'array') {
            return MapperAbstract.attributePropertyAdditionalMap[attribute];
        }

        if (typeof MapperAbstract.attributePropertyAdditionalMap[attribute] === 'function') {
            return MapperAbstract.attributePropertyAdditionalMap[attribute](value);
        }

        return [MapperAbstract.attributePropertyAdditionalMap[attribute]];
    };

    /**
     * Sub-classes can override this to provide extra cass styles for the DOM container
     */
    MapperAbstract.prototype.getExtraDomStyles = function(element)
    {
        return [];
    };

    /**
     * Sub-classes can override this to provide extra attributes mapped to CSS properties
     */
    MapperAbstract.prototype.getExtraAttributePropertyMap = function()
    {
        return {};
    };

    /**
     * @abstract
     */
    MapperAbstract.prototype.getHtmlContents = function(element)
    {
        throw 'RuntimeException: InvoiceDesigner\\Template\\Element\\MapperAbstract::getHtmlContents() should be overridden by sub-class';
    };

    return MapperAbstract;
});