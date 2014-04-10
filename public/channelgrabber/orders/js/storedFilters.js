define(
    ['popup/mustache', 'cg-mustache'],
    function(Popup, CGMustache) {
        var StoredFilters = function(notifications, filters, savedFilterList) {
            this.getNotifications = function() {
                return notifications;
            };

            var filters = $(filters)
            this.getFilters = function() {
                return filters;
            };

            var savedFilterList = $(savedFilterList)
            this.getSavedFilterList = function() {
                return savedFilterList;
            };

            var popup;
            this.getPopup = function() {
                return popup;
            };

            var init = function() {
                var self = this;
                var savedFilterList = self.getSavedFilterList();

                savedFilterList.on("click.storedFilters", "li a", function() {
                    self.activateFilter.call(self, $(this).closest("li"));
                });
                savedFilterList.on("click.storedFilters", "li .close", function() {
                    self.deleteFilter.call(self, $(this).closest("li"));
                });

                popup = new Popup(
                    savedFilterList.data("popup")
                );

                popup.getElement().on("click.storedFilters", ".save", function() {
                    var name = $.trim(popup.getElement().find("input.name").val());
                    if (!name.length) {
                        return;
                    }
                    self.saveFilter.call(self, name, popup.getElement().data("filter"));
                    popup.hide();
                });
                popup.getElement().on("click.storedFilters", ".cancel", function() {
                    popup.hide();
                });
            };
            init.call(this);
        };

        StoredFilters.prototype.bindSaveTo = function(element) {
            var self = this;
            $(element).bind("click.storedFilters", function() {
                self.saveCurrentFilter.call(self);
            });
        };

        StoredFilters.prototype.saveCurrentFilter = function() {
            this.getPopup().getElement().data("filter", []);
            this.getPopup().show();
        };

        StoredFilters.prototype.saveFilter = function(name, filter) {
            this.getNotifications().notice("Saving Filter");

            var listElement = $();
            CGMustache.get().fetchTemplate(this.getSavedFilterList().data("template"), function(template, cgmustache) {
                listElement = $(cgmustache.renderTemplate(template, {
                    name: name,
                    filter: filter
                }));
            });

            var self = this;
            $.ajax({
                url: self.getSavedFilterList().data("save"),
                type: "POST",
                dataType: "json",
                data: {
                    name: name,
                    filter: filter
                },
                success: function(data) {
                    self.handleAjaxSuccess.call(self, data, listElement);
                },
                error: function(request) {
                    self.handleAjaxError.call(self, request, listElement);
                }
            });
        };

        StoredFilters.prototype.activateFilter = function(listElement) {

        };

        StoredFilters.prototype.deleteFilter = function(listElement) {
            this.getNotifications().notice("Removing Filter");

            var self = this;
            $.ajax({
                url: self.getSavedFilterList().data("remove"),
                type: "POST",
                dataType: "json",
                data: {
                    name: $(listElement).data("name")
                },
                success: function(data) {
                    self.handleAjaxSuccess.call(self, data, listElement);
                },
                error: function(request) {
                    self.handleAjaxError.call(self, request, listElement);
                }
            });
        };

        StoredFilters.prototype.handleAjaxSuccess = function(data, listElement) {
            this.handleResponse.call(this, data, listElement);
        };

        StoredFilters.prototype.handleAjaxError = function(request, listElement) {
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

        StoredFilters.prototype.handleResponse = function(json, listElement) {
            if (json.display_exceptions && json.message) {
                this.getNotifications().error(json.message);
                return;
            }

            if (json.error) {
                this.getNotifications().error(json.error);
                return;
            }

            if (json.saved) {
                this.getSavedFilterList().append(listElement);
                this.getSavedFilterList().find(".empty-list").addClass("hidden");
                this.getNotifications().success("Filter Saved");
            } else if (json.removed) {
                listElement.remove();
                if (!this.getSavedFilterList().find("li").not(".empty-list").length) {
                    this.getSavedFilterList().find(".empty-list").removeClass("hidden");
                }
                this.getNotifications().success("Filter Removed");
            } else {
                this.getNotifications().error("An error has occurred, please try again");
            }
        };

        return StoredFilters;
    }
);