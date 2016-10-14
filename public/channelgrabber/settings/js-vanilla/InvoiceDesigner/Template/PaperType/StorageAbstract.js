define(['InvoiceDesigner/Template/PaperType/Mapper'], function(paperTypeMapper)
{
    var StorageAbstract = function()
    {
        var mapper = paperTypeMapper;

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