define(['./Mapper'], function(mapper)
{
    var StorageAbstract = function()
    {
        var mapper = mapper;

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