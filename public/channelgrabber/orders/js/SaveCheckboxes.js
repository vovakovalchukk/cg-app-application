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
    }

    return SaveCheckboxes();
});