$(function(){
    var af = new AjaxForm();
    af.init();
});

function AjaxForm() { 
    var formElement     = null;
    var formSelector    = "form.ajaxPost";
    var loadingSelector = ".loading";
    var formStatus      = null;
    var fieldErrors     = [];
    var redirectUrl;

    this.init = function(){
        handleSubmit();
    };

    var handleSubmit= function(){
        $(formSelector).submit(function(e) {
            e.preventDefault();
            formElement = $(this);
            n.clearNotifications($('#main-notifications'));
            resetErrors($(this));
            showLoading();

            formElement.ajaxSubmit({
                "dataType": "json",
                "success": function(data) {
                    processResponse(data);
                    hideLoading();
                },
                "error": function(request, statusMessage, error) {
                    handleError(request, statusMessage, error);
                    hideLoading();
                },
                "complete": function() {
                    formElement.find("input").removeAttr("disabled");
                    hideLoading();
                }
            });
        });
    };

    var hideLoading = function(){
        formElement.find(loadingSelector).hide();
    };

    var showLoading = function(){
        formElement.find(loadingSelector).html('Saving').show();
    };

    var processResponse= function(data){
        processRedirect(data);

        hideLoading();
        if (!data.valid){
            for(var elementName in data.messages) {
                for (var error in data.messages[elementName]) {
                    showFieldError(elementName, data.messages[elementName][error]);
                }
            }
            n.showNotification($('#main-notifications'), 'error', 'There are errors in the form, please try again!', false);
            return;
        }
        if (!data.status && data.display_exceptions) {
            n.showNotification($('#main-notifications'), 'error', data.message, false);
            return;
        }
        if (!data.status) {
            n.showNotification($('#main-notifications'), 'error', 'There has been an error processing the request, please try again', false);
            return;
        }
        n.showNotification($('#main-notifications'), 'success', data.status, true);
        if (data.url) {
            formElement.attr("action", data.url);
        }
    };

    var processRedirect = function(data) {
        var delay = 0;

        if(typeof(data['redirect']) != "undefined" && data['redirect']['href'].length > 0){
            if(typeof(data['redirect']['delay']) != "undefined"){
                delay = data['redirect']['delay'];
            }
            window.setTimeout(function(){
                window.location.href = data['redirect']['href'];
            },delay);
        }
    }

    var handleError = function(request, statusMessage, error) {
        hideLoading();
        if (statusMessage == 'error') {
            try {
                processResponse($.parseJSON(request.responseText));
                return
            } catch (parseError) {}
        }
        n.showNotification($('#main-notifications'), 'error', 'There has been an error connecting to the server, please try again', false);
    }

    var showFieldError= function(elementName, error){
        formElement.find('[name='+elementName+']').after("<div style='display:none' class='tooltip error-tooltip clearfix'><span class='icon'>!</span> <span class='content'><span class='arrow'></span>"+error+"</span></div>");
        formElement.find('.error-tooltip').each(function(){
            $(this).slideDown();
        })
    };

    var resetErrors= function(){
        formElement.find(".error-tooltip").slideUp('', function(){ $(this).remove()});
    };
}