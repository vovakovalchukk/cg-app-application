define(['element/moreButton', 'element/ElementCollection'], function(MoreButton, elementCollection) {

    var Filters = function(filters, filterList)
    {
        this.savedFilters = {};

        filters = $(filters)
        this.getFilters = function() {
            return filters;
        };

        filterList = $(filterList);
        this.getFilterList = function() {
            return filterList;
        };

        this.applyFilterValues = function(filterName, filterOptions)
        {
            var self = this;
            var element = elementCollection.get(filterName);
            if (element) {
                element.setValue(filterOptions);
                Filters.pendingFilters--;
                self.updateFilters();
            }
        };

        this.handleFilterAdding = function()
        {
            var self = this;
            $(document).bind('ElementCollection.attach.after', function(e, filter) {
                var filterName = filter.getElementName();
                if (self.savedFilters.hasOwnProperty(filterName)) {
                    self.applyFilterValues(filterName, self.savedFilters[filterName]);
                    delete self.savedFilters[filterName];
                }
            });
        };

        var init = function() {
            var self = this;
            self.getFilterList().on("click.filters", "li a", function() {
                self.activateFilter.call(self, $(this).closest("li"));
            });
            this.handleFilterAdding();
        };
        init.call(this);
    };

    Filters.pendingFilters = 0;

    Filters.prototype.clearFilters = function()
    {
        this.getFilters().find(".more label[data-filter-name]").each(function() {
            var checkbox = $(this).find(":checkbox");
            if (checkbox.is(":checked")) {                 
                checkbox.click();   
            }
        });   
    };

    Filters.prototype.prepareFilterValues = function(filterName, filterOptions)
    {
        this.savedFilters[filterName] = filterOptions;
    };

    Filters.prototype.activateFilter = function(listElement) 
    {
        var filters = $(listElement).data("filter");
        this.clearFilters();
        MoreButton.removeFilters();
        this.getFilters().trigger("reset");
        
        Filters.pendingFilters = Object.keys(filters).length;

        for (var filterName in filters) {
            var filterOptions = filters[filterName];
            var filter = elementCollection.get(filterName);
            
            if (!filter) {
                if (MoreButton.addFilter(filterName)) {
                    this.prepareFilterValues(filterName, filterOptions);
                }
            } else {
                this.applyFilterValues(filterName, filterOptions);
            }
        };
    };

    Filters.prototype.updateFilters = function() {
        if (Filters.pendingFilters == 0) {
            this.getFilters().trigger("apply");
        }
    };

    return Filters;
});