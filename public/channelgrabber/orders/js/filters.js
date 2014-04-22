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

            this.getFilters().trigger("reset");
            
            
            console.log(filter);
            //return false;

            this.getFilters().find(".more label[data-filter-name]").each(function() {
                var checked = $(this).find(":checkbox").is(":checked");
                var selected = ($.inArray($(this).data("filter-name"), filter.optional) >= 0);

                if (checked != selected) {
                    $(this).find(":checkbox").click();
                }
            });

            
            for (var key in filter.filters) {
                var value = filter.filters[key];
                console.log(key);
                console.log(value);
                
                if (value instanceof Array) {
                    // click each from the list
                    var filter = this.getFilters().find("div[data-element-name="+key+"]");
                    console.log(filter);
                                        
                    
                } else {
                    console.log(key+' set to '+value);
                    this.getFilters().find(":input["+key+"]").attr(value);
                }
                
            }
            
            
            return true;

            this.getFilters().find(":input[name]").each(function() {
                var name = $(this).attr("name");
                
                console.log(name);
                
                if (filter.filters[name] == undefined) {
                    return;
                }
                $(this).val(filter.filters[name]);
            });

            //this.getFilters().trigger("apply");
        };

        return Filters;
    }
);