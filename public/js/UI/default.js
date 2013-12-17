$(function(){
    var djs = new DefaultJS();
    djs.init();
});

function DefaultJS() { 
    this.init = function(){
        handleUnauthorisedAjax();
    };
    
    var handleUnauthorisedAjax = function(){
        $(document).bind('ajaxComplete', function(event, XMLHttpRequest, ajaxOptions) {
            if(XMLHttpRequest.status == 401) {
                location.reload();
            }
        });
    };
}