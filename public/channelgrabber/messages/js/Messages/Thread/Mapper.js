define([
    'Messages/Thread/Entity',
    'Messages/Thread/Message/Collection',
    'Messages/Thread/Message/Mapper'
], function(
    Thread,
    MessageCollection,
    messageMapper
) {
    var Mapper = function()
    {
        this.getMessageMapper = function()
        {
            return messageMapper;
        };
    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: Messages\Thread\Message\Mapper::fromJson must be passed a JSON object';
        }
        var json = JSON.parse(JSON.stringify(json));

        var messages = new MessageCollection();
        for (var index in json.messages) {
            var message = messageMapper.fromJson(json.messages[index]);
            messages.attach(message);
        }

        var thread = new Thread(
            json.id,
            json.channel,
            json.accountId,
            json.accountName,
            json.status,
            json.created,
            json.createdFuzzy,
            json.updated,
            json.updatedFuzzy,
            json.name,
            json.externalUsername,
            json.assignedUserId,
            json.assignedUserName,
            json.subject,
            json.externalId,
            json.ordersCount,
            json.ordersLink,
            messages
        );
        return thread;
    };

    return new Mapper();
});
