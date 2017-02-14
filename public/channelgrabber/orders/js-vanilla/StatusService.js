define([
    'cg-mustache'
], function(
    CGMustache
) {
    function StatusService() {
    }

    StatusService.STATUS_CONTAINER_SELECTOR = '.current-account-status';
    StatusService.STATUS_ELEMENT_SELECTOR = '.current-account-status > span';


    StatusService.prototype.refresh = function (newStatus) {
        console.log(newStatus);
        $(StatusService.STATUS_ELEMENT_SELECTOR).removeClass();
        $(StatusService.STATUS_ELEMENT_SELECTOR).addClass('status '+newStatus);
        $(StatusService.STATUS_ELEMENT_SELECTOR).text(newStatus.ucfirst());
    };

    return new StatusService;
});