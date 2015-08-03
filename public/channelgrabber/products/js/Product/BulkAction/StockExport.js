define(function() {
    var StockExport = function(notifications, selector) {
        this.getNotifications = function() {
            return notifications;
        };

        this.getSelector = function() {
            return selector;
        };
    };

    StockExport.prototype.action = function() {
        var that = this;
        $.ajax({
            context: that,
            url: that.getUrl(),
            type: "POST",
            success : function(response, status, request) {
                var disp = request.getResponseHeader('Content-Disposition');
                if (!(disp && disp.search('attachment') != -1)) {
                    that.getNotifications().error("Failed to download Stock CSV");
                    return;
                }

                var form = $('<form method="POST" action="' + that.getUrl() + '">');
                $('body').append(form);
                form.submit();
                that.getNotifications().success("Downloading Stock CSV...");
            },
            error: function(error, textStatus, errorThrown) {
                that.getNotifications().ajaxError(error, textStatus, errorThrown);
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
