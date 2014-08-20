define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract) {
    var PPI = function() {
        var additionalData = {
            option: undefined
        };

        ElementAbstract.call(this, additionalData);
        this.set('type', 'PPI', true);
        this.set('borderWidth', undefined, true);

        var ignoredAttributes = ['backgroundColour', 'borderWidth', 'borderColour'];
        baseInspectableAttributes = this.getBaseInspectableAttributes();
        for (var key in ignoredAttributes) {
            var index = baseInspectableAttributes.indexOf(ignoredAttributes[key]);
            if (index >= 0) {
                baseInspectableAttributes.splice(index, 1);
            }
        }

        this.getOption = function() {
            return this.get('option');
        };

        this.setOption = function(newOption) {
            this.set('option', newOption);
            return this;
        };
    };

    PPI.prototype = Object.create(ElementAbstract.prototype)

    return PPI;
});