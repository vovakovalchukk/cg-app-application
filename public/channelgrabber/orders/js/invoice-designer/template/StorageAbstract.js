define(['./Mapper'], function(Mapper) {
    var StorageAbstract = function(mapper)
    {
        if (!(mapper instanceof Mapper)) {
            throw 'InvalidArgumentException: InvoiceDesigner Storage must be passed an instance of Mapper';
        }

        this.getMapper = function()
        {
            return mapper;
        };

        this.enforceMethod('fetch');
        this.enforceMethod('save');
    };

    return StorageAbstract;
});