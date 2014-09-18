define([
    'Product/Service',
    'KeyPress'
], function (
    service,
    KeyPress
) {
    var Search = function()
    {
    };

    Search.prototype.init = function(inputSelector, buttonSelector, baseUrl)
    {
        var self = this;
        $(inputSelector).off('keypress').on('keypress', function(event){
            if (event.which == KeyPress.ENTER) {
                self.search(inputSelector);
            }
        });
        $(buttonSelector).off('click').on('click', function(event){
            self.search(inputSelector);
        });
        service.init(baseUrl);
    };

    Search.prototype.search = function(inputSelector)
    {
        service.refresh();
    };

    return new Search();
});