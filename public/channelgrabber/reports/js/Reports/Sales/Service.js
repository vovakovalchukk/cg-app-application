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
            this.loadEventListeners();

            this.updateChart = this.updateChart.bind(this);
            this.redrawChart = this.redrawChart.bind(this);
        }

        loadEventListeners() {
            $("#filters input[data-action='apply-filters']").on("click", (function () {
                this.updateChart();
            }).bind(this));

            $(".channel-filter input[type='checkbox']").on("click", (function (e) {
                let $object = $(e.currentTarget);
                let datasetKey = $object.attr('name');
                let visible = $object.is(':checked');
                this.changeDatasetVisibility(datasetKey, visible);
            }).bind(this));

            $("input[name='data-type']").on("change", (function() {
                this.updateChart();
            }).bind(this));
        }

        updateChart() {
            this.ajax.fetch(this.buildRequestData(), this.redrawChart);
        }

        redrawChart(data) {
            this.chart.update(data);
            this.resetFilters();
        }

        buildRequestData() {
            this._resetRequestData();
            this.buildFiltersRequestData();
            this.requestData.strategyType = $("input[name='data-type']:checked").data('type');
            return this.requestData;
        }

        buildFiltersRequestData() {
            $("#filters :input[name]").each(function(index, input) {
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
            $(".channel-filter input[type='checkbox']").prop( "checked", true);
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
