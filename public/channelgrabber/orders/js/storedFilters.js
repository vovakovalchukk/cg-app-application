define(function() {
    var StoredFilters = function(filters, savedFilterList) {
        this.getFilters = function() {
            return $(filters);
        };

        this.getSavedFilterList = function() {
            return $(savedFilterList);
        };

        var init = function() {
            var self = this;
            self.getSavedFilterList().on("click.storedFilters", "li a", function() {
                self.activateFilter.call($(this).closest("li"));
            });
            self.getSavedFilterList().on("click.storedFilters", "li .close", function() {
                self.deleteFilter.call($(this).closest("li"));
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

    };

    StoredFilters.prototype.activateFilter = function(listElement) {

    };

    StoredFilters.prototype.deleteFilter = function(listElement) {

    };

    return StoredFilters;
});