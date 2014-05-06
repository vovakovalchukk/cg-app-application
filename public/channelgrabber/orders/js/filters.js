define(['element/moreButton'], function(MoreButton) {

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
            require(['filterCollection'], function(FilterCollection) {
                var filterObject = FilterCollection.get(filterName);
                if (filterObject != undefined) {
                    filterObject.setValue(filterOptions);
                }
                Filters.pendingFilters--;
                self.updateFilters();
            });
        };

        this.handleFilterAdding = function()
        {
            var self = this;
            $(document).bind('filterCollection.attach.after', function(e, filter) {

                var filterName = filter.getElementName();
                if (Filters.savedFilters.hasOwnProperty(filterName)) {
                    self.applyFilterValues(filterName, Filters.savedFilters[filterName]);
                    delete Filters.savedFilters[filterName];
                }
            });
        };

        var init = function() {
            var self = this;
            self.getFilterList().on("click.filters", "li a", function() {
                self.activateFilter.call(self, $(this).closest("li"));
            });
            self.handleFilterAdding();
        };
        init.call(this);
    };

    Filters.pendingFilters = 0;
    Filters.savedFilters = {};

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
        Filters.savedFilters[filterName] = filterOptions;
    };

    Filters.prototype.handleFilterAdding = function() 
    {
        var self = this;
        $(document).bind('filterCollection.attach.after', function(e, filter) {

            var filterName = filter.getElementName();
            if (Filters.savedFilters.hasOwnProperty(filterName)) {
                self.applyFilterValues(filterName, Filters.savedFilters[filterName]);
                delete Filters.savedFilters[filterName];
            }
        });
    };

    Filters.prototype.activateFilter = function(listElement) 
    {
        var filters = $(listElement).data("filter");

        this.clearFilters();
        this.getFilters().trigger("reset");
        
        Filters.pendingFilters = Object.keys(filters).length;

        for (var filterName in filters) {
            var filterOptions = filters[filterName];
            
            var filter = this.getFilters().find(".more label[data-filter-name=" + filterName + "]");
            
            if (filter.length != 0) {
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