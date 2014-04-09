define(
    ['popup/mustache'],
    function(Popup) {
        var StoredFilters = function(filters, savedFilterList) {
            this.getFilters = function() {
                return $(filters);
            };

            this.getSavedFilterList = function() {
                return $(savedFilterList);
            };

            var popup;
            var init = function() {
                var self = this;
                var savedFilterList = self.getSavedFilterList();

                savedFilterList.on("click.storedFilters", "li a", function() {
                    self.activateFilter.call($(this).closest("li"));
                });
                savedFilterList.on("click.storedFilters", "li .close", function() {
                    self.deleteFilter.call($(this).closest("li"));
                });

                popup = new Popup(
                    savedFilterList.data("template")
                );

                popup.getElement().on("click.storedFilters", ".save", function() {
                    var name = $.trim(popup.getElement().find("input.name").val());
                    if (!name.length) {
                        return;
                    }
                    self.saveFilter.call(self, name, popup.getElement().data("filter"));
                });
                popup.getElement().on("click.storedFilters", ".cancel", function() {
                    popup.hide();
                });
            };

            this.getPopup = function() {
                return popup;
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
            this.getPopup().show();
        };

        StoredFilters.prototype.saveFilter = function(name, filter) {

        };

        StoredFilters.prototype.activateFilter = function(listElement) {

        };

        StoredFilters.prototype.deleteFilter = function(listElement) {

        };

        return StoredFilters;
    }
);