define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract'
], function(
    requireModule,
    DomListenerAbstract
) {
    let inputs = {
        rows: null,
        columns: null,
        width: null,
        height: null
    };

    let MultiPage = function() {
        DomListenerAbstract.call(this);
    };

    MultiPage.prototype = Object.create(DomListenerAbstract.prototype);

    MultiPage.ROWS_INPUT_SELECTOR = 'multiPageRows';
    MultiPage.COLUMNS_INPUT_SELECTOR = 'multiPageColumns';
    MultiPage.WIDTH_INPUT_SELECTOR = 'multiPageWidth';
    MultiPage.HEIGHT_INPUT_SELECTOR = 'multiPageHeight';

    MultiPage.prototype.init = function(module) {
        DomListenerAbstract.prototype.init.call(this, module);

        inputs.rows = document.getElementById(MultiPage.ROWS_INPUT_SELECTOR);
        inputs.columns = document.getElementById(MultiPage.COLUMNS_INPUT_SELECTOR);
        inputs.width = document.getElementById(MultiPage.WIDTH_INPUT_SELECTOR);
        inputs.height = document.getElementById(MultiPage.HEIGHT_INPUT_SELECTOR);

        inputs.rows.addEventListener('change', event => {
            const value = event.target.value;
            this.getModule().setGridTrack(value, 'rows');
        });

        inputs.columns.addEventListener('change', event => {
            const value = event.target.value;
            this.getModule().setGridTrack(value, 'columns');
        });

        inputs.width.addEventListener('change', event => {
            if (event.target.value < 1) {
                event.target.value = null;
            }
            const value = event.target.value;
            this.getModule().setDimension('width', value);
        });

        inputs.height.addEventListener('change', event => {
            if (event.target.value < 1) {
                event.target.value = null;
            }
            const value = event.target.value;
            this.getModule().setDimension('height', value);
        });
    };

    MultiPage.prototype.getInputs = function() {
        return inputs;
    };

    return MultiPage;
});