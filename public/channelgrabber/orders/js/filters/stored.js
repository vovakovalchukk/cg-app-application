define(
    ['filters', 'popup/mustache', 'cg-mustache', 'filterCollection'],
    function(Filters, Popup, CGMustache, FilterCollection) {
        var StoredFilters = function(notifications, filters, filterList) {
            Filters.call(this, filters, filterList);

            this.getNotifications = function() {
                return notifications;
            };

            var popup;
            this.getPopup = function() {
                return popup;
            };

            var init = function() {
                var self = this;
                var filterList = self.getFilterList();

                filterList.on("click.storedFilters", "li .close", function() {
                    self.deleteFilter.call(self, $(this).closest("li"));
                });

                popup = new Popup(
                    filterList.data("popup")
                );

                popup.getElement().on("callback.storedFilters", function(event) {
                    popup.getElement().find("input.name").focus();
                });
                popup.getElement().on("keypress.storedFilters", "input.name", function(event) {
                    if (event.which !== 13) {
                        return;
                    }
                    popup.getElement().find(".save").click();
                });
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

        StoredFilters.prototype = Object.create(Filters.prototype);

        StoredFilters.prototype.bindSaveTo = function(element) {
            var self = this;
            $(element).bind("click.storedFilters", function() {
                self.saveCurrentFilter.call(self);
            });
        };

        StoredFilters.prototype.getCurrentFilter = function()
        {
            return FilterCollection.getCollectionValues();
        };

        StoredFilters.prototype.saveCurrentFilter = function()
        {
            console.log(this.getCurrentFilter());
            this.getPopup().getElement().data("filter", this.getCurrentFilter());
            this.getPopup().show();
        };

        StoredFilters.prototype.saveFilter = function(name, filter) {
            this.getNotifications().notice("Saving Filter");

            var listElement = $();
            CGMustache.get().fetchTemplate(this.getFilterList().data("template"), function(template, cgmustache) {
                listElement = $(cgmustache.renderTemplate(template, {
                    name: name,
                    filter: JSON.stringify(filter)
                }));
            });

            var self = this;
            $.ajax({
                url: self.getFilterList().data("save"),
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

        StoredFilters.prototype.deleteFilter = function(listElement) {
            this.getNotifications().notice("Removing Filter");

            var self = this;
            $.ajax({
                url: self.getFilterList().data("remove"),
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
                this.getFilterList().find("li[data-name='" + listElement.data("name") + "']").remove();
                this.getFilterList().append(listElement);
                this.getFilterList().find(".empty-list").addClass("hidden");
                this.getNotifications().success("Filter Saved");
            } else if (json.removed) {
                listElement.remove();
                if (!this.getFilterList().find("li").not(".empty-list").length) {
                    this.getFilterList().find(".empty-list").removeClass("hidden");
                }
                this.getNotifications().success("Filter Removed");
            } else {
                this.getNotifications().error("An error has occurred, please try again");
            }
        };

        return StoredFilters;
    }
);