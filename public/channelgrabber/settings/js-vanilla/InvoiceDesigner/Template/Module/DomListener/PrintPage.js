define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract'
], function(
    requireModule,
    DomListenerAbstract
) {
    let PrintPage = function()
    {
        DomListenerAbstract.call(this);
    };

    PrintPage.prototype = Object.create(DomListenerAbstract.prototype);

    PrintPage.TOP_MARGIN_INPUT_SELECTOR = 'printPageTopMargin';
    PrintPage.BOTTOM_MARGIN_INPUT_SELECTOR = 'printPageBottomMargin';
    PrintPage.LEFT_MARGIN_INPUT_SELECTOR = 'printPageLeftMargin';
    PrintPage.RIGHT_MARGIN_INPUT_SELECTOR = 'printPageRightMargin';

    let inputs = {
        top: null,
        bottom: null,
        left: null,
        right: null
    };

    PrintPage.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        //
        inputs.top = document.getElementById(PrintPage.TOP_MARGIN_INPUT_SELECTOR);
        inputs.bottom = document.getElementById(PrintPage.BOTTOM_MARGIN_INPUT_SELECTOR);
        inputs.left = document.getElementById(PrintPage.LEFT_MARGIN_INPUT_SELECTOR);
        inputs.right = document.getElementById(PrintPage.RIGHT_MARGIN_INPUT_SELECTOR);

        inputs.top.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('top', value);
        });

        inputs.bottom.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('bottom', value);
        });

        inputs.left.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('left', value);
        });

        inputs.right.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('right', value);
        });
    };

    PrintPage.prototype.getInputs = function(){
        return inputs;
    };

    return PrintPage;
});