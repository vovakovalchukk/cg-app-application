define([

], function(
    
) {
    var Entity = function(
        id,
        accountId,
        created,
        createdFuzzy,
        name,
        externalUsername,
        body,
        threadId,
        personType
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

        this.getAccountId = function()
        {
            return accountId;
        };

        this.setAccountId = function(newAccountId)
        {
            accountId = newAccountId;
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

        this.getBody = function()
        {
            return body;
        };

        this.setBody = function(newBody)
        {
            body = newBody;
            return this;
        };

        this.getThreadId = function()
        {
            return threadId;
        };

        this.setThreadId = function(newThreadId)
        {
            threadId = newThreadId;
            return this;
        };

        this.getPersonType = function()
        {
            return personType;
        };

        this.setPersonType = function(newPersonType)
        {
            personType = newPersonType;
            return this;
        };
    };

    return Entity;
});