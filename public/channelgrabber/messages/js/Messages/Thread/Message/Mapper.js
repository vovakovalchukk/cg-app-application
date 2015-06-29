define([
    'Messages/Thread/Message/Entity'
], function(
    Message
) {
    var Mapper = function()
    {

    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: Messages\Thread\Message\Mapper::fromJson must be passed a JSON object';
        }
        var json = JSON.parse(JSON.stringify(json));

        var message = new Message(
            json.id,
            json.accountId,
            json.created,
            json.createdFuzzy,
            json.name,
            json.externalUsername,
            json.body,
            json.threadId
        );
        return message;
    };

    return new Mapper();
});