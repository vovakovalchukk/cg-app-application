define([], function ()
{
    var Pagination = function(service)
    {
        this.getService = function()
        {
            return service;
        }

        var init = function()
        {
            this.listenForPageLinkClicks();
        };
        init.call(this);
    };

    Pagination.SELECTOR_PAGINATION = '#product-pagination';

    Pagination.prototype.listenForPageLinkClicks = function()
    {
        var service = this.getService();
        $(Pagination.SELECTOR_PAGINATION).on('page-selected', function(event, page)
        {
            service.pageSelected(page);
        });
        return this;
    };

    return Pagination;
});
