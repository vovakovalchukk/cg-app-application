define(
    ['filters'],
    function(Filters) {
        var StoredBatches = function(notifications, batches, batchesList) {
            Filters.call(this, batches, batchesList);

            var init = function() {
                var self = this;
                var filtersList = self.getFilterList();

                if (Array.isArray(this.getListItemNames())) {
                    self.setupSearch('batches-search');
                }

                filtersList.on("click.batch", 'li .close', function(event) {
                    self.deleteBatch.call(self, $(this).closest("li"));
                    event.stopImmediatePropagation();
                });
            };

            this.getNotifications = function() {
                return notifications;
            };

            init.call(this);
        };

        StoredBatches.prototype = Object.create(Filters.prototype);

        StoredBatches.prototype.deleteBatch = function(listElement) {
            this.getNotifications().notice("Removing batch");

            var self = this;
            $.ajax({
                url: self.getFilterList().data("remove"),
                type: "POST",
                dataType: "json",
                data: {
                    id: $(listElement).data("id")
                },
                success: function(data) {
                    self.handleAjaxSuccess.call(self, data, listElement);
                },
                error: function(request) {
                    self.handleAjaxError.call(self, request, listElement);
                }
            });
        };

        StoredBatches.prototype.handleAjaxError = function(request, listElement) {
            if (request.getResponseHeader('Content-Type').indexOf('json') > -1) {
                try {
                    this.handleResponse.call(this, $.parseJSON(request.responseText), listElement);
                    return;
                } catch (parseError) {
                    // Display default error notification
                }
            }

            this.getNotifications().error(
                "There has been an error connecting to the server, please try again"
            );
        };

        StoredBatches.prototype.removeJson = function(listElement) {
            listElement.remove();
            if (!this.getFilterList().find("li").not(".empty-list").length) {
                this.getFilterList().find(".empty-list").removeClass("hidden");
            }
            this.getNotifications().success("Batch Removed");
        };

        StoredBatches.prototype.handleAjaxSuccess = function(json, listElement) {
            if (json.display_exceptions && json.message) {
                this.getNotifications().error(json.message);
                return;
            }

            if (json.error) {
                this.getNotifications().error(json.error);
                return;
            }

            if (json.removed) {
                listElement.remove();
                this.removeJson.call(this, listElement);
            } else {
                this.getNotifications().error("An error has occurred, please try again");
            }
        };

        return StoredBatches;
    }
);