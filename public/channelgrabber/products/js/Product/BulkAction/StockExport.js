define(function() {
    var StockExport = function(notifications, selector) {
        this.getNotifications = function() {
            return notifications;
        }

        this.getSelector = function() {
            return selector;
        }
    };

    StockExport.prototype.action = function() {
        var that = this;
        $.ajax({
            context: this,
            url: this.getUrl(),
            type: "POST",
            success: function(response, status, xhr) {
                var filename = "stock.csv";
                var type = xhr.getResponseHeader('Content-Type');
                var blob = new Blob([response], { type: type  });
                var URL = window.URL || window.webkitURL;
                var downloadUrl = URL.createObjectURL(blob);

                var a = document.createElement("a");

                if (typeof a.download === 'undefined') {
                    window.location = downloadUrl;
                }

                a.href = downloadUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
            },
            error: function(error, textStatus, errorThrown) {
                return that.getNotifications().ajaxError(error, textStatus, errorThrown);
            }
        })
    };

    StockExport.prototype.getFormElement = function(orders) {
        return $("<form><input name='stockExport' value='' /></form>")
            .attr("action", this.getUrl())
            .attr("method", "POST")
            .hide();
    };

    StockExport.prototype.getUrl = function() {
        return this.getElement().data("url") || "";
    };

    StockExport.prototype.getElement = function() {
        return $(this.getSelector());
    };

    return StockExport;
});
