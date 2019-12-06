define(
    ['filters', 'popup/mustache', 'cg-mustache', 'Filters/FilterCollection', 'element/ElementCollection'],
    function(Filters, Popup, CGMustache, filterCollection, elementCollection) {
        var StoredFilters = function(notifications, filters, filterList) {
            Filters.call(this, filters, filterList);

            let popup = null;

            const init = function() {
                var self = this;

                if (Array.isArray(this.getListItemNames())) {
                    self.setupSearch('saved-filters-search');
                    document.getElementById('savedFilters').style.display = 'block';
                }

                self.getFilterList().on("click.storedFilters", "li .close", function(event) {
                    self.deleteFilter.call(self, $(this).closest("li"));
                    event.stopImmediatePropagation();
                });

                popup = new Popup(
                    self.getFilterList().data("popup")
                );
                setupPopup.call(this); // call
            };

            this.getNotifications = function() {
                return notifications;
            };

            this.getPopup = function() {
                return popup;
            };

            var setupPopup = function() {
                var self = this;
                popup.getElement().on("callback.storedFilters", function(event) {
                    focusPopup();
                });

                popup.getElement().on("keypress.storedFilters", "input#filter-name", function(event) {
                    if (event.which !== 13) {
                        return;
                    }
                    savePopup.call(self);
                });

                popup.getElement().on("click.storedFilters", ".save", function() {
                    savePopup.call(self);
                });

                popup.getElement().on("click.storedFilters", ".cancel", function() {
                    popup.hide();
                });
            };

            var focusPopup = function() {
                popup.getElement().find("input#filter-name").focus();
            };

            var savePopup = function() {
                var name = $.trim(popup.getElement().find("input#filter-name").val());
                if (!name.length) {
                    return;
                }
                this.saveFilter.call(this, name);
                popup.hide();
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

        StoredFilters.prototype.getCurrentFilterValues = function()
        {
            var collectionValues = {};
            for (var filterName in filterCollection.getFilters()) {
                var filter = elementCollection.get(filterName);
                if (filter) {
                    collectionValues[filterName] = filter.getValue();
                }
            }
            return collectionValues;
        };

        StoredFilters.prototype.saveCurrentFilter = function()
        {
            this.getPopup().show();
        };

        StoredFilters.prototype.saveFilter = function(name) {
            this.getNotifications().notice("Saving Filter");
            var filters = JSON.stringify(this.getCurrentFilterValues());
            var listElement = $();
            var synchronous = true;
            CGMustache.get().fetchTemplate(this.getFilterList().data("template"), function(template, cgmustache) {
                listElement = $(cgmustache.renderTemplate(template, {
                    name: name,
                    filter: filters
                }));
            }, synchronous);

            var self = this;
            $.ajax({
                url: self.getFilterList().data("save"),
                type: "POST",
                dataType: "json",
                data: {
                    name: name,
                    filter: filters
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

        StoredFilters.prototype.saveJson = function(listElement) {
            this.getFilterList().find("li[data-name='" + listElement.data("name") + "']").remove();
            this.getFilterList().append(listElement);
            this.getFilterList().find(".empty-list").addClass("hidden");
            this.getNotifications().success("Filter Saved");
        };

        StoredFilters.prototype.removeJson = function(listElement) {
            listElement.remove();
            if (!this.getFilterList().find("li").not(".empty-list").length) {
                this.getFilterList().find(".empty-list").removeClass("hidden");
            }
            this.getNotifications().success("Filter Removed");
        };
        
        StoredFilters.prototype.handleAjaxSuccess = function(json, listElement) {
            if (json.display_exceptions && json.message) {
                this.getNotifications().error(json.message);
                return;
            }

            if (json.error) {
                this.getNotifications().error(json.error);
                return;
            }

            if (json.saved) {
                this.saveJson.call(this, listElement);
            } else if (json.removed) {
                listElement.remove();
                this.removeJson.call(this, listElement);
            } else {
                this.getNotifications().error("An error has occurred, please try again");
            }
        };

        return StoredFilters;
    }
);