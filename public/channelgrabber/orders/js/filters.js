define(
    [],
    function() {
        var Filters = function(filters, filterList) {
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

        Filters.prototype.activateFilter = function(listElement) {
            var filter = $(listElement).data("filter");

            this.getFilters().find(".more a[data-filter-name]").each(function() {
                var checked = $(this).find(":checkbox").is(":checked");
                var selected = ($.inArray($(this).data("filter-name"), filter.optional) >= 0);

                if (checked != selected) {
                    $(this).click();
                }
            });

            this.getFilters().find(":input[name]").each(function() {
                var name = $(this).attr("name");
                if (filter.filters[name] == undefined) {
                    return;
                }
                $(this).val(filter.filters[name]);
            });

            this.getFilters().find("[data-action='apply-filters']").click();
        };

        return Filters;
    }
);