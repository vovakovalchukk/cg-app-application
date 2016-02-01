define(function() {
    var SaveCheckboxes = function()
    {
        var savedCheckboxes;
        var savedCheckAll;

        this.getSavedCheckboxes = function()
        {
            return savedCheckboxes;
        };

        this.setSavedCheckboxes = function(checkboxes)
        {
            savedCheckboxes = checkboxes;
            return this;
        };

        this.getSavedCheckAll = function()
        {
            return savedCheckAll;
        };

        this.setSavedCheckAll = function(checkAll)
        {
            savedCheckAll = checkAll;
            return this;
        };
    };

    SaveCheckboxes.prototype.refreshCheckboxes = function(dataTable)
    {
        var self = this;
        if(!dataTable) {
            return
        }
        dataTable.one('fnDrawCallback', function () {
            self.getSavedCheckboxes().forEach(function (singleCheckbox) {
                var checkboxObj = $('#checkbox-' + singleCheckbox);
                checkboxObj.attr('checked', true);
                checkboxObj.closest('tr').addClass('selected');
            });
            if (self.getSavedCheckAll()) {
                $('#' + dataTable.attr('id') + '-select-all').prop('checked', true);
            }
        });

    };

    return new SaveCheckboxes();
});