define([
    'element/moreButton',
    'element/ElementCollection',
    'quick-score'
], function(
    MoreButton,
    elementCollection,
    quickScore
) {
    var Filters = function(filters, filterList)
    {
        this.savedFilters = {};

        filters = $(filters);
        this.getFilters = function() {
            return filters;
        };

        filterList = $(filterList);
        this.getFilterList = function() {
            return filterList;
        };

        this.getFilterListItems = function() {
            return [...this.getFilterList()[0].children].filter((childNode) => {
                return !!childNode.attributes['data-name'];
            });
        };

        this.getListItemNames = function() {
            return this.getFilterListItems().map((childNode) => {
                return this.getNameValueFromNode(childNode)
            });
        };

        this.getNameValueFromNode = function(node) {
            return node.attributes['data-name'].value;
        };

        this.applyFilterItemDisplayBasedOnSearchTerm = function(searchTerm) {
            const itemNames = this.getFilterListItems().map((node) => this.getNameValueFromNode(node));

            let searchClass = new quickScore.QuickScore(itemNames);
            let results = searchClass.search(searchTerm);
            
            for (let node of this.getFilterListItems()) {
                node.style.display = 'none';
            }

            for (let result of results) {
                for (let node of this.getFilterListItems()) {
                    if (this.getNameValueFromNode(node).indexOf(result.item) === -1) {
                        continue;
                    }
                    node.style.display = 'block';
                }
            }
        };

        this.applyDisplayPropToListItemsFromSearch = (event) => {
            this.applyFilterItemDisplayBasedOnSearchTerm(event.target.value);
        };

        this.setupSearch = function(searchInputId) {
            const searchElement = document.getElementById(searchInputId);
            searchElement.addEventListener('keyup', this.applyDisplayPropToListItemsFromSearch);
        };

        optionalFilters = {};
        $('.custom-select[data-element-name=more] li').each(function() {
            config = $(this).data('value');
            if (config) {
                optionalFilters[config['name']] = config;
            }
        });

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
        return this;
    };

    Filters.pendingFilters = 0;

    Filters.prototype.clearFilters = function()
    {
        var element = elementCollection.get("more");
        if (element) {
            element.setValue([]);
        }
    };

    Filters.prototype.prepareFilterValues = function(filterName, filterOptions)
    {
        this.savedFilters[filterName] = filterOptions;
    };

    Filters.prototype.activateFilter = function(listElement) 
    {
        var filters = $(listElement).data("filter");

        this.clearFilters();
        this.getFilters().trigger("reset");
        
        Filters.pendingFilters = Object.keys(filters).length;

        for (var filterName in filters) {
            var filterOptions = filters[filterName];
            var filter = elementCollection.get(filterName);
            if (!filter) {
                var template = optionalFilters[filterName]['template'];
                var variables = optionalFilters[filterName]['variables'];
                if (MoreButton.addFilter(filterName, template, variables)) {
                    this.prepareFilterValues(filterName, filterOptions);
                }
            } else {
                this.applyFilterValues(filterName, filterOptions);
            }
        }
    };

    Filters.prototype.updateFilters = function() {
        if (Filters.pendingFilters == 0) {
            this.getFilters().trigger("apply");
        }
    };

    return Filters;
});
