define(['BulkActionAbstract'], function(BulkActionAbstract)
{
    function ProductLinkExport(selector) {

        this.getSelector = function() {
            return selector;
        };

        BulkActionAbstract.call(this);
    };

    ProductLinkExport.prototype = Object.create(BulkActionAbstract.prototype);

    ProductLinkExport.prototype.invoke = function() {
        this.submitRequest();
    };

    ProductLinkExport.prototype.submitRequest = function() {
        var self = this;
        $.ajax({
            context: self,
            url: $(self.getSelector()).data("url"),
            type: "GET",
            dataType: 'json',
            processData: false,
            contentType: false,
            success : function(data) {
                self.getNotificationHandler().success("Your export will be emailed to " +  data.email + ". This can take up to 10 minutes depending on the amount of your products and complexity of your product links.", true, 2400);
            },
            error: function(error, textStatus, errorThrown) {
                self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return ProductLinkExport;
});
