define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract) {
    var PPI = function() {
        var additionalData = {
            option: undefined
        };

        ElementAbstract.call(this, additionalData);
        this.set('type', 'PPI', true);

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