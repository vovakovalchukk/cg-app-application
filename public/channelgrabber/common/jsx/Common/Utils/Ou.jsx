define([
], function(
) {
    var Ou = function(options) {
        this.ouList = options.ouList;
    };

    Ou.prototype.getTradingCompanies = function () {
        return this.ouList;
    };

    return Ou;
});