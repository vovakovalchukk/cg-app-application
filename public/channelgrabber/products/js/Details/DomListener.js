define([], function() {
    function DomListener(service)
    {
        this.getService = function()
        {
            return service;
        };
        this.init();
    }

    DomListener.HOLDER = '.details-table';
    DomListener.INPUT = 'input';
    DomListener.EVENT = 'save.details';

    DomListener.prototype.init = function()
    {
        this.listenForDetailsSave();
    };

    DomListener.prototype.listenForDetailsSave = function()
    {
        var self = this;
        var selector = DomListener.HOLDER + ' ' + DomListener.INPUT;
        $(document)
            .off(DomListener.EVENT, selector)
            .on(DomListener.EVENT, selector, function() {
                self.getService().updateDetail(
                    undefined,
                    $(this).attr('name'),
                    $(this).val(),
                    undefined
                );
            });
    };

    return DomListener;
});
