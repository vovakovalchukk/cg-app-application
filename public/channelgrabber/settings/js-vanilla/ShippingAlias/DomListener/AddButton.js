define([
    'ShippingAlias/DomManipulator'
],
function(domManipulator)
{
    var AddButton = function() { };

    AddButton.ADD_BUTTON_SELECTOR = '#addButtonSelector';

    AddButton.prototype.init = function(module)
    {
        var self = this;

        $(AddButton.ADD_BUTTON_SELECTOR).click(function () {
            domManipulator.prependAlias();
        });
    };

    return new AddButton();
});