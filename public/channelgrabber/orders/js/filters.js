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

        var init = function() {
            var self = this;
            self.getFilterList().on("click.filters", "li a", function() {
                self.activateFilter.call(self, $(this).closest("li"));
            });
        };
        init.call(this);
    };

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
        $(document).bind('filterCollection.attach.after', function(e, filter) {

            var filterName = filter.getElementName();
            if (Filters.savedFilters.hasOwnProperty(filterName)) {
                Filters.prototype.applyFilterValues(filterName, Filters.savedFilters[filterName]);
                delete Filters.savedFilters[filterName];
            }
        });
    };

    Filters.prototype.applyFilterValues = function(filterName, filterOptions)
    {
        require(['filterCollection'], function(FilterCollection) {
            var filterObject = FilterCollection.get(filterName);
            if (filterObject != undefined) {
                filterObject.setValue(filterOptions);
            }
        });
    };

    Filters.prototype.activateFilter = function(listElement) 
    {
        var filters = $(listElement).data("filter").filters;

        this.clearFilters();
        this.getFilters().trigger("reset");

        for (var filterName in filters) {
            var filterOptions = filters[filterName];
            
            if (filterName == 'purchaseDate[from' || filterName == 'purchaseDate[to' || filterName == 'search') {
                continue;
            }
            
            filter = this.getFilters().find(".more label[data-filter-name=" + filterName + "]");
            
            if (filter.length != 0) {
                if (MoreButton.prototype.addFilter(filterName)) {
                    this.prepareFilterValues(filterName, filterOptions);;
                }
            } else {
                this.applyFilterValues(filterName, filterOptions);
            }
        };
    };

    Filters.prototype.handleFilterAdding();

    return Filters;
});