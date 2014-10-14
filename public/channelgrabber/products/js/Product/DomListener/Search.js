define([
    'Product/Service',
    'KeyPress',
    'ElementWatcher'
], function (
    service,
    KeyPress,
    elementWatcher
) {
    var Search = function()
    {
    };

    Search.prototype.init = function(inputSelector, buttonSelector, baseUrl)
    {
        var self = this;
        elementWatcher.onInitialise(function() {
            self.listen(inputSelector, buttonSelector, baseUrl);
        });
    };

    Search.prototype.listen = function(inputSelector, buttonSelector, baseUrl)
    {
        var self = this;
        $(inputSelector).off('keypress').on('keypress', function(event){
            if (event.which == KeyPress.ENTER) {
                self.search();
            }
        });
        $(buttonSelector).off('click').on('click', function(event){
            self.search();
        });
        service.init(baseUrl);
    };

    Search.prototype.search = function()
    {
        service.refresh();
    };

    return new Search();
});