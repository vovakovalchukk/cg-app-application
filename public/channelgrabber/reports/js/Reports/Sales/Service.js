define([
    'Reports/Sales/ChartJs',
    'Reports/OrderCounts/Ajax'
], function(
    ChartJs,
    Ajax
) {
    class Service {
        constructor() {
            this.chart = ChartJs;
            this.ajax = Ajax;

            this.requestData = {};
            this.selectors = {
                channelFilterInput: ".channel-filter input[type='checkbox']",
                dataTypeFilter: "input[name='data-type']",
                applyFiltersButton: "#filters input[data-action='apply-filters']",
                filterInputs: "#filters :input[name]"
            };

            this.loadEventListeners();

            this.updateChart = this.updateChart.bind(this);
            this.redrawChart = this.redrawChart.bind(this);
        }

        loadEventListeners() {
            $(this.selectors.applyFiltersButton).on("click", (function () {
                this.updateChart();
            }).bind(this));

            $(this.selectors.channelFilterInput).on("click", (function (e) {
                let $object = $(e.currentTarget);
                let datasetKey = $object.attr('name');
                let visible = $object.is(':checked');
                this.changeDatasetVisibility(datasetKey, visible);
            }).bind(this));

            $(this.selectors.dataTypeFilter).on("change", (function() {
                this.updateChart();
            }).bind(this));
        }

        updateChart() {
            this.ajax.fetch(this.buildRequestData(), this.redrawChart);
        }

        redrawChart(data) {
            this.chart.update(data);
            this.resetFilters();
            this.updateFilters();
        }

        buildRequestData() {
            this._resetRequestData();
            this.buildFiltersRequestData();
            this.requestData.strategyType = $(this.selectors.dataTypeFilter + ':checked').data('type');
            return this.requestData;
        }

        buildFiltersRequestData() {
            $(this.selectors.filterInputs).each(function(index, input) {
                let $input = $(input);
                let value = $.trim($input.val());
                if (!value.length) {
                    return;
                }
                let name = $input.attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
                this.requestData[name] = value;
            }.bind(this));
        }

        changeDatasetVisibility(datasetKey, visibility) {
            return this.chart.changeDatasetVisibility(datasetKey, visibility);
        }

        resetFilters() {
            $(this.selectors.channelFilterInput).prop( "checked", true);
        }

        updateFilters() {
            $(this.selectors.channelFilterInput).each(function(key, element) {
                let $element = $(element);
                let colour = this.chart.getColorByDatasetKey($element.attr('name'));
                $element.closest('.channel-filter').find('span.logo').css('color', colour);
            }.bind(this));
        }

        _resetRequestData() {
            this.requestData = {
                'strategy': ['channel', 'total'],
                'unitType': 'day'
            };
        }
    }

    return new Service();
});
