define(['InvoiceDesigner/Template/Mapper'], function(templateMapper)
{
    var StorageAbstract = function()
    {
        var mapper = templateMapper;

        this.getMapper = function()
        {
            return mapper;
        };

        this.setMapper = function(newMapper)
        {
            mapper = newMapper;
            return this;
        };
    };

    return StorageAbstract;
});