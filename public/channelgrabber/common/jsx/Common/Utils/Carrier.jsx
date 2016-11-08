define([
], function(
) {
    var Carrier = function(options) {
        this.carriersList = options.carriersList;
    };

    Carrier.prototype.getCarriers = function () {
        return this.carriersList;
    };

    return Carrier;
});