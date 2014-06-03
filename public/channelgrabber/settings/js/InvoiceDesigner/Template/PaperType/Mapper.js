define([
    'require',
    'InvoiceDesigner/Template/PaperType/Entity'
], function(
    require, PaperTypeEntity
) {
    var Mapper = function() {};

    Mapper.prototype.fromArray = function(array)
    {
        if (!Array.isArray(array)) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\PaperType\Mapper::fromArray must be passed an array object';
        }

        var collection = [];
        var populating = true;

        array.forEach(function(paperTypeArray) {
            var paperType = new PaperTypeEntity();
            paperType.hydrate(paperTypeArray, populating);
            collection.push(paperType);
        });

        return collection;
    };

    return new Mapper();
});