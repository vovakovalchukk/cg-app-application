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
    DomListener.ROW = 'tr.detail';
    DomListener.INPUT = 'input';
    DomListener.EVENT = 'save.details';

    DomListener.prototype.init = function()
    {
        this.listenForDetailsSave();
    };

    DomListener.prototype.listenForDetailsSave = function()
    {
        var self = this;
        var selector = DomListener.HOLDER + ' ' + DomListener.ROW + ' ' + DomListener.INPUT;
        $(document)
            .off(DomListener.EVENT, selector)
            .on(DomListener.EVENT, selector, function() {
                self.getService().updateDetail(
                    $(this).closest(DomListener.ROW).data('id'),
                    $(this).attr('name'),
                    $(this).val(),
                    $(this).closest(DomListener.ROW).data('sku')
                );
            });
    };

    return DomListener;
});
