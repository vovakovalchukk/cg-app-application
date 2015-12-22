define([
    'Messages/Thread/Message/Collection'
], function(
    MessageCollection
) {
    var Entity = function(
        id,
        channel,
        accountId,
        accountName,
        status,
        created,
        createdFuzzy,
        updated,
        updatedFuzzy,
        name,
        externalUsername,
        assignedUserId,
        assignedUserName,
        subject,
        externalId,
        ordersCount,
        ordersLink,
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

        this.getAccountName = function()
        {
            return accountName;
        };

        this.setAccountName = function(newAccountName)
        {
            accountName = newAccountName;
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

        this.getCreatedFuzzy = function()
        {
            return createdFuzzy;
        };

        this.setCreatedFuzzy = function(newCreatedFuzzy)
        {
            createdFuzzy = newCreatedFuzzy;
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

        this.getUpdatedFuzzy = function()
        {
            return updatedFuzzy;
        };

        this.setUpdatedFuzzy = function(newUpdatedFuzzy)
        {
            updatedFuzzy = newUpdatedFuzzy;
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

        this.getAssignedUserName = function()
        {
            return assignedUserName;
        };

        this.setAssignedUserName = function(newAssignedUserName)
        {
            assignedUserName = newAssignedUserName;
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

        this.getOrdersCount = function()
        {
            return ordersCount;
        };

        this.setOrdersCount = function(newOrdersCount)
        {
            ordersCount = newOrdersCount;
            return this;
        };

        this.getOrdersLink = function()
        {
            return ordersLink;
        };

        this.setOrdersLink = function(newOrdersLink)
        {
            ordersLink = newOrdersLink;
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
