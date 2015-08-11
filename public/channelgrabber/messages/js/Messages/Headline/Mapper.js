define([
    'Messages/Headline/Entity'
], function(
    Headline
) {
    var Mapper = function()
    {
    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: Messages\Headline\Mapper::fromJson must be passed a JSON object';
        }
        var json = JSON.parse(JSON.stringify(json));

        var headline = new Headline(
            json.organisationUnitId,
            json.myMessages,
            json.unassigned,
            json.assigned,
            json.resolved
        );
        return headline;
    };

    return new Mapper();
});