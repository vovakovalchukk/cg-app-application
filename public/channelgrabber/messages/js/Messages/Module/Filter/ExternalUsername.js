define([
    'Messages/Module/FilterAbstract'
], function(
    FilterAbstract
) {
    var ExternalUsername = function(filterModule, externalUsername)
    {
        var type = 'externalUsername';

        this.getExternalUsername = function()
        {
            return externalUsername;
        };

        this.setExternalUsername = function(newExternalUsername)
        {
            externalUsername = newExternalUsername;
            return this;
        };

        this.getType = function()
        {
            return type;
        };

        // Must have defined getType() before this as it depends on it
        FilterAbstract.call(this, filterModule);
    };

    ExternalUsername.prototype = Object.create(FilterAbstract.prototype);

    ExternalUsername.prototype.getFilterData = function()
    {
        return {
            externalUsername: this.getExternalUsername()
        };
    };

    ExternalUsername.prototype.setValue = function(value)
    {
        this.setExternalUsername(value);
        return this;
    };

    return ExternalUsername;
});