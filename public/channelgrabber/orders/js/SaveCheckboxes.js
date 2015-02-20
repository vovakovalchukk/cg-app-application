define(function() {
    var SaveCheckboxes = function()
    {
        var savedCheckboxes;

        this.getSavedCheckboxes = function()
        {
            return savedCheckboxes;
        };

        this.setSavedCheckboxes = function(checkboxes)
        {
            savedCheckboxes = checkboxes;
        };
    };

    SaveCheckboxes.prototype.refreshCheckboxes = function(dataTable)
    {
        var self = this;
        if(dataTable) {
            dataTable.one('fnDrawCallback', function () {
                self.getSavedCheckboxes().forEach(function (singleCheckbox) {
                    var checkboxObj = $('#checkbox-' + singleCheckbox);
                    checkboxObj.attr('checked', true);
                    checkboxObj.closest('tr').addClass('selected');
                });
            });
        };
    };

    return new SaveCheckboxes();
});