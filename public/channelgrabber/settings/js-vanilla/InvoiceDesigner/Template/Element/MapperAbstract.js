define([
    'cg-mustache',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/Element/Service'
], function(
    CGMustache,
    domManipulator,
    elementService
) {
    var MapperAbstract = function()
    {
        var optionalAttribs = ['backgroundColour', 'borderWidth', 'borderColour'];
        this.getOptionalAttribs = function()
        {
            return optionalAttribs;
        };

        var cgMustache = new CGMustache();
        this.getCGMustache = function()
        {
            return cgMustache;
        };

        var manipulator = domManipulator;
        this.getDomManipulator = function()
        {
            return manipulator;
        };

        var service = elementService;
        this.getService = function()
        {
            return service;
        };
    };

    MapperAbstract.ELEMENT_DOM_CLASS = 'template-element';
    MapperAbstract.ELEMENT_DOM_WRAPPER_CLASS = elementService.getElementDomWrapperClass();
    MapperAbstract.ELEMENT_DOM_ID_PREFIX = 'template-element-';
    MapperAbstract.ELEMENT_TEMPLATE_PATH = '/channelgrabber/settings/template/InvoiceDesigner/Template/Element/';

    MapperAbstract.getDomIdPrefix = function()
    {
        return MapperAbstract.ELEMENT_DOM_ID_PREFIX;
    };

    MapperAbstract.getDomId = function(element)
    {
        return MapperAbstract.ELEMENT_DOM_ID_PREFIX+element.getId();
    };

    MapperAbstract.getDomWrapperId = function(element)
    {
        return MapperAbstract.getDomId(element)+'-wrapper';
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

        var wrapperCssStyle = this.getDomWrapperStyles(element).join('; ');
        var wrapperCssClasses = this.getDomWrapperClasses(element).join(' ');
        var cssClasses = this.getDomClasses(element).join(' ');
        var cssStyle = this.getDomStyles(element).join('; ');
        var htmlContents = this.getHtmlContents(element);

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH+'abstract.mustache';
        var data = {
            id: domId,
            wrapperStyles: wrapperCssStyle,
            wrapperClasses: wrapperCssClasses,
            classes: cssClasses,
            styles: cssStyle,
            contents: htmlContents,
            resizable: element.isResizable(),
            minWidth: element.getMinWidth(),
            maxWidth: element.getMaxWidth(),
            minHeight: element.getMinHeight(),
            maxHeight: element.getMaxHeight()
        };
        var html = this.renderMustacheTemplate(templateUrl, data);

        return html;
    };

    MapperAbstract.prototype.renderMustacheTemplate = function(templateUrl, data)
    {
        var synchronous = true;
        var html;
        this.getCGMustache().fetchTemplate(templateUrl, function(template, cgMustache)
        {
            html = cgMustache.renderTemplate(template, data);
        }, synchronous);
        return html;
    };

    MapperAbstract.prototype.getDomWrapperClasses = function(element)
    {
        return [MapperAbstract.ELEMENT_DOM_WRAPPER_CLASS];
    };

    MapperAbstract.prototype.getDomClasses = function(element)
    {
        var domClasses = [MapperAbstract.ELEMENT_DOM_CLASS];
        if (element.getType()) {
            domClasses.push('template-element-' + element.getType().toLowerCase());
        }
        var extraDomClasses = this.getExtraDomClasses(element);
        for (var key in extraDomClasses) {
            domClasses.push(extraDomClasses[key]);
        }
        return domClasses;
    };

    MapperAbstract.prototype.getDomWrapperStyles = function(element)
    {
        var position = {
            top: element.getY().mmToPx(),
            left: element.getX().mmToPx()
        };
        position = this.getService().removeDomWrapperGapFromDimensions(position);
        var domStyles = [
            'top: ' + position.top.pxToMm() + 'mm',
            'left: ' + position.left.pxToMm() + 'mm'
        ];
        return domStyles;
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
        var size = {
            width: element.getWidth().mmToPx(),
            height: element.getHeight().mmToPx()
        };

        size = this.getService().addDomWrapperGapToDimensions(size);

        var domStyles = [
            'width: ' + size.width.pxToMm() + 'mm',
            'height: ' + size.height.pxToMm() + 'mm'
        ];

        var optionalAttribs = this.getOptionalAttribs();
        domStyles = this.addOptionalDomStyles(element, optionalAttribs, domStyles);
        var extraDomStyles = this.getExtraDomStyles(element);
        for (var key in extraDomStyles) {
            domStyles.push(extraDomStyles[key]);
        }

        if (element.getErrorBorder()) {
            domStyles.push('border: 2px red dashed');
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

    /**
     * @abstract
     */
    MapperAbstract.prototype.createElement = function()
    {
        throw 'RuntimeException: InvoiceDesigner\\Template\\Element\\MapperAbstract::createElement() should be overridden by sub-class';
    };

    return MapperAbstract;
});