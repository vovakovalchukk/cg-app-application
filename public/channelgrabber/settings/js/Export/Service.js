define([], function() {
    var Service = function() {
        self.getNotifications = function() {
            return n;
        };
    };

    Service.prototype.notifyCsvGeneration = function() {
        var fadeOut = true;
        self.getNotifications().notice("Generating CSV", fadeOut);
    };

    return new Service();
});
