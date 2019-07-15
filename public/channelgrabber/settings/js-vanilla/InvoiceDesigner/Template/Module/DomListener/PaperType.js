define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract',
    'element/customSelect'
], function(
    requireModule,
    DomListenerAbstract,
    CustomSelect
) {

    var PaperType = function()
    {
        DomListenerAbstract.call(this);
    };

    PaperType.CONTAINER_ID = 'paperTypeModule';
    PaperType.CHECKBOX_ID = 'inverseLabelPosition';
    PaperType.HEIGHT_ID = 'paperHeight';
    PaperType.WIDTH_ID = 'paperWidth';

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
//        debugger;

        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(document).on('change', paperTypeDropdownId, function (event, selectBox, id) {
            var isInverse = $("#" + PaperType.CHECKBOX_ID).is(":checked");
            self.getModule().selectionMade(id, isInverse);
        });

        $(document).on('change', `#${PaperType.HEIGHT_ID}`, (event) => {
            console.log('in height change');
            
            
           let desiredValue = event.target.value;
           self.getModule().changePaperDimension("height", desiredValue);
        });

        $(document).on('change', `#${PaperType.WIDTH_ID}`, (event) => {
           let desiredValue = event.target.value;
           self.getModule().changePaperDimension("width", desiredValue);
        });

        $("#" + PaperType.CHECKBOX_ID).click(function() {
            var selectedId = $("#" + PaperType.CONTAINER_ID + " input[type=hidden]").val();
            var isInverse = $("#" + PaperType.CHECKBOX_ID).is(":checked");
            self.getModule().selectionMade(selectedId, isInverse);
        });
    };

    return PaperType;
});