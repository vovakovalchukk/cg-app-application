function Notifications() {
    defaultParent = '#main-notifications';
    selector = '.notifications';
    closeButtonSelector = '.close';
    fadeOutDelay = 5000;

    this.init =  function(element){
        prepareNotifications(element);
        handleClose();
    };

    this.clearNotifications = function(parentElement){
        parentElement.find(selector).slideUp();
        parentElement.find(selector).children().remove();
    };

    this.showNotification = function(parentElement, type, message, doHide){
        parentElement.find(selector).append('<div class="'+type+' clearfix"><span class="icon"><span>!</span></span><span class="content">' + message + '</span><a href="#" class="close">Close X</a></div>').fadeIn('1000', function(){
            $(this).find('.success').delay(fadeOutDelay).fadeOut();
        });
    };

    var prepareNotifications = function(element){
        element.prepend("<div class='notifications' style='display:none'></div>");
    };

    var closeNotification = function(element){
        element.slideUp();
    };

    var handleClose = function(){
        $(document).on('click', '.close', function(e) {
            e.preventDefault();
            closeNotification($(this).parent());
            return false;
        });
    }
}

var n = new Notifications();
$(function(){
   n.init($('#main-notifications')); 
});