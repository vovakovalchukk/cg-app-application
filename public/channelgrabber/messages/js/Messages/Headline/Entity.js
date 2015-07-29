define([
    
], function(
    
) {
    var Entity = function(
        organisationUnitId,
        myMessages,
        unassigned,
        assigned,
        resolved
    ) {
        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.setOrganisationUnitId = function(newOrganisationUnitId)
        {
            organisationUnitId = newOrganisationUnitId;
            return this;
        };

        this.getMyMessages = function()
        {
            return myMessages;
        };

        this.setMyMessages = function(newMyMessages)
        {
            myMessages = newMyMessages;
            return this;
        };

        this.getUnassigned = function()
        {
            return unassigned;
        };

        this.setUnassigned = function(newUnassigned)
        {
            unassigned = newUnassigned;
            return this;
        };

        this.getAssigned = function()
        {
            return assigned;
        };

        this.setAssigned = function(newAssigned)
        {
            assigned = newAssigned;
            return this;
        };

        this.getResolved = function()
        {
            return resolved;
        };

        this.setResolved = function(newResolved)
        {
            resolved = newResolved;
            return this;
        };
    };

    return Entity;
});