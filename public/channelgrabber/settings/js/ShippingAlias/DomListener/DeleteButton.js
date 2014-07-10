
define([
    'ShippingAlias/DomManipulator'
],
function(domManipulator)
{
    var DeleteButton = function() { };

    DeleteButton.DELETE_BUTTON_SELECTOR = '.shipping-alias-delete';

    DeleteButton.prototype.init = function(module)
    {
        var self = this;

        $(document).on("click", DeleteButton.DELETE_BUTTON_SELECTOR, function() {
            var root = domManipulator.getDomSelectorAliasContainer();

            // $(this).parentsUntil(root).find(".inputbox").val() // Alias name
            $(this).parentsUntil(root).remove(); // Remove from the browser, not rom ajax
        });
    };

    return new DeleteButton();
});
