define(function()
{
    var MapperAbstract = function()
    {

    };

    MapperAbstract.attributePropertyMap = {
        x: "left",
        y: "top"
    };

    MapperAbstract.prototype.toHtml = function(element)
    {
        var domId = this.getDomId(element);
        var cssClasses = this.getDomClasses(element).join(' ');
        var cssStyle = this.getDomStyles(element).join('; ');

        var html = '<div id="'+domId+'" class="'+cssClasses+'" style="'+cssStyle+'">\n';
        html += this.getHtmlContents(element);
        html += '\n</div>';

        return html;
    };

    MapperAbstract.prototype.getDomId = function(element)
    {
        return 'template-element-'+element.getId();
    };

    MapperAbstract.prototype.getDomClasses = function(element)
    {
        var domClasses = ['template-element', 'template-element-'+element.getType()];
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
        var optionalAttribs = ['x', 'y', 'backgroundColour', 'borderWidth', 'borderColour'];
        for (var key in optionalAttribs) {
            var attribute = optionalAttribs[key];
            var property = this.elementAttributeToCssProperty(attribute);
            var getter = 'get' + attribute.ucfirst();
            if (element[getter]()) {
                var value = this.elementAttributeValueToCssPropertyValue(element[getter]());
                domStyles.push(property+': '+value);
                if (attribute === 'borderColour') {
                    domStyles.push('border-style: solid');
                }
            }
        }
        var extraDomStyles = this.getExtraDomStyles(element);
        for (var key in extraDomStyles) {
            domStyles.push(extraDomStyles[key]);
        }
        return domStyles;
    };

    MapperAbstract.prototype.elementAttributeToCssProperty = function(attribute)
    {
        var map = MapperAbstract.attributePropertyMap;
        if (map[attribute]) {
            return map[attribute];
        }
        return attribute.camelCaseToDashed().replace('colour', 'color');
    };

    MapperAbstract.prototype.elementAttributeValueToCssPropertyValue = function(value)
    {
        if (typeof value === 'number' && value !== '' && !isNaN(value)) {
            return value+'mm';
        }
        return value;
    };

    /**
     * Sub-classes can override this to provide extra cass styles for the DOM container
     */
    MapperAbstract.prototype.getExtraDomStyles = function(element)
    {
        return [];
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