define(["Settings/Export/Service"], function(service) {
    var DomListener = function() {};

    DomListener.DOM_SELECTOR_CSV = 'a.csvExport';

    DomListener.prototype.init = function() {
        $(DomListener.DOM_SELECTOR_CSV).off('click').on('click', function() {
            service.notifyCsvGeneration();
        });
    };

    return new DomListener();
});
