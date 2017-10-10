var ekmRegistrationStatusCheckAttempts = 1;
var ekmRegistrationMaxStatusCheckAttempts = 3;
var EkmRegistration = {
    checkStatus: function () {
        var urlParams = new URLSearchParams(window.location.search);
        var token = urlParams.get('token');
        var endpoint = 'https://'+$(location).attr('hostname')+'/ekm/register';
        var ajaxEndpoint = 'https://'+$(location).attr('hostname')+'/ekm/register/ajax';
        var checkInterval = (1000 * 30);
        if (ekmRegistrationStatusCheckAttempts >= ekmRegistrationMaxStatusCheckAttempts) {
            window.location.href = endpoint+'?token='+encodeURIComponent(token)+'&status=0';
        }
        $("#ekm-registration-loading").show();
        $.ajax({
            "url" : ajaxEndpoint+'?token='+encodeURIComponent(token),
            "type" : "GET",
            "success" : function(data) {
                if (data['complete'] === true) {
                    window.location.href = endpoint+'?token='+token;
                }
            },
            "error" : function() {
                ekmRegistrationStatusCheckAttempts += 1;
                setTimeout(EkmRegistration.checkStatus, checkInterval);
            },
            "complete" : function() {
                ekmRegistrationStatusCheckAttempts += 1;
                setTimeout(EkmRegistration.checkStatus, checkInterval);
            }
        });
    }
}

$( document ).ready(function() {
    EkmRegistration.checkStatus();
});
