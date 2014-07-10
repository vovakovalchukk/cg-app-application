define([
    'ShippingAlias/DomManipulator'
],
function(domManipulator)
{
    var AliasNameInput = function() { };

    AliasNameInput.ALIAS_NAME_INPUT_SELECTOR = '.order-inputbox-holder';

    AliasNameInput.prototype.init = function(module)
    {
        var self = this;

        $(document).on("keyup", AliasNameInput.ALIAS_NAME_INPUT_SELECTOR, function(event, data) {
            var input = this;
            delay(function(){
                // console.log($(input).find('.inputbox').val());
            }, 1000 );
        });
    };

    return new AliasNameInput();
});

/**
 * https://gist.github.com/ericchen/947727
 */
var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();
