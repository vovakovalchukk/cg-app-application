define([], function()
{
    function Service(dataTable)
    {
        this.getDataTable = function()
        {
            return dataTable;
        };

        var init = function()
        {
            // TODO
        };
        init.call(this);
    }

    return Service;
});