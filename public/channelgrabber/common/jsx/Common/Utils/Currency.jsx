define([
], function(
) {
    var Currency = function(options) {
        this.currenciesList = options.currenciesList;
    };

    Currency.prototype.getCurrencies = function () {
        return this.currenciesList;
    };

    return Currency;
});