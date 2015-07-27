define([
    'Messages/Thread/Message/Mapper'
], function(
    mapper
) {
    var StorageAbstract = function()
    {
        this.getMapper = function()
        {
            return mapper;
        };
    };

    return StorageAbstract;
});