define(['BulkActionAbstract'], function(BulkActionAbstract) {
    var StockExport = function(selector) {
        BulkActionAbstract.call(this);

        this.getSelector = function() {
            return selector;
        };
    };

    StockExport.prototype = Object.create(BulkActionAbstract.prototype);

    StockExport.prototype.init = function() {
        BulkActionAbstract.prototype.init.call(this, this.getSelector());
    };

    StockExport.prototype.invoke = function() {
        var that = this;
        $.ajax({
            context: that,
            url: that.getUrl(),
            type: "POST",
            success : function(response, status, request) {
                var disp = request.getResponseHeader('Content-Disposition');
                if (!(disp && disp.search('attachment') != -1)) {
                    that.getNotificationHandler().error("Failed to download Stock CSV");
                    return;
                }

                var form = $('<form method="POST" action="' + that.getUrl() + '">');
                $('body').append(form);
                form.submit();
                that.getNotificationHandler().success("Downloading Stock CSV...");
            },
            error: function(error, textStatus, errorThrown) {
                that.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    StockExport.prototype.getUrl = function() {
        return this.getElement().data("url") || "";
    };

    StockExport.prototype.getElement = function() {
        return $(this.getSelector());
    };

    return StockExport;
});
