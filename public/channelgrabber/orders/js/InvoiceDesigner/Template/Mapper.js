define([], function()
{
    var Mapper = function()
    {

    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Mapper::fromJson must be passed a JSON object';
        }

        /*
         * TODO (CGIV-2009)
         */
    };

    Mapper.prototype.toHtml = function(template)
    {
        /*
         * TODO (CGIV-2009)
         */
    };

    return new Mapper();
});