define([
    'require',
    'InvoiceDesigner/Template/PaperType/Entity',
], function(require)
{
    var Mapper = function() {};

    Mapper.prototype.fromArray = function(array)
    {

        console.log("mapper array: " + array);
        if (!Array.isArray(array)) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\PaperType\Mapper::fromArray must be passed an array object';
        }

        var collection = [];
        var populating = true;

        array.forEach(function(paperTypeArray) {
            var paperType = require('InvoiceDesigner/Template/PaperType/Entity');
            collection.push(paperType.hydrate(paperTypeArray, populating))
        });

        return collection;
    };

    return new Mapper();
});