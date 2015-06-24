define([
    'Messages/Thread/Message/Collection'
], function(
    MessageCollection
) {
    var Entity = function(
        id,
        channel,
        accountId,
        status,
        created,
        updated,
        name,
        externalUsername,
        assignedUserId,
        subject,
        externalId,
        messages
    ) {
        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
            return this;
        };

        this.getChannel = function()
        {
            return channel;
        };

        this.setChannel = function(newChannel)
        {
            channel = newChannel;
            return this;
        };

        this.getAccountId = function()
        {
            return accountId;
        };

        this.setAccountId = function(newAccountId)
        {
            accountId = newAccountId;
            return this;
        };

        this.getStatus = function()
        {
            return status;
        };

        this.setStatus = function(newStatus)
        {
            status = newStatus;
            return this;
        };

        this.getCreated = function()
        {
            return created;
        };

        this.setCreated = function(newCreated)
        {
            created = newCreated;
            return this;
        };

        this.getUpdated = function()
        {
            return updated;
        };

        this.setUpdated = function(newUpdated)
        {
            updated = newUpdated;
            return this;
        };

        this.getName = function()
        {
            return name;
        };

        this.setName = function(newName)
        {
            name = newName;
            return this;
        };

        this.getExternalUsername = function()
        {
            return externalUsername;
        };

        this.setExternalUsername = function(newExternalUsername)
        {
            externalUsername = newExternalUsername;
            return this;
        };

        this.getAssignedUserId = function()
        {
            return assignedUserId;
        };

        this.setAssignedUserId = function(newAssignedUserId)
        {
            assignedUserId = newAssignedUserId;
            return this;
        };

        this.getSubject = function()
        {
            return subject;
        };

        this.setSubject = function(newSubject)
        {
            subject = newSubject;
            return this;
        };

        this.getExternalId = function()
        {
            return externalId;
        };

        this.setExternalId = function(newExternalId)
        {
            externalId = newExternalId;
            return this;
        };

        this.getMessages = function()
        {
            if (!messages) {
                messages = new MessageCollection();
            }
            return messages;
        };

        this.setMessages = function(newMessages)
        {
            messages = newMessages;
            return this;
        };
    };

    return Entity;
});