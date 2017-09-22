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

            this.init();

            this.updateChart = this.updateChart.bind(this);
            this.redrawChart = this.redrawChart.bind(this);
        }

        init() {
            $("#filters input[data-action='apply-filters']").on("click", (function () {
                this.ajax.fetch(this.buildRequestData(), (function (data) {
                    this.redrawChart(data);
                }).bind(this));
            }).bind(this));

            $(".channel-filter input[type='checkbox']").on("click", (function (e) {
                let $object = $(e.currentTarget);
                let datasetKey = $object.attr('name');
                let visible = $object.is(':checked');
                console.log(visible);
                this.changeDatasetVisibility(datasetKey, visible);
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
            let requestData = this.buildFiltersRequestData();
            requestData.strategyType = 'count';
            requestData.strategy = ['channel', 'total'];
            requestData.unitType = 'day';
            return requestData;
        }

        buildFiltersRequestData() {
            let filters = {};
            $("#filters :input[name]").each(function() {
                let value = $.trim($(this).val());
                if (!value.length) {
                    return;
                }
                let name = $(this).attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
                filters[name] = value;
            });
            return filters;
        }

        changeDatasetVisibility(datasetKey, visibility) {
            return this.chart.changeDatasetVisibility(datasetKey, visibility);
        }

        resetFilters() {
            $(".channel-filter input[type='checkbox']").prop( "checked", true);
        }
    }

    return new Service();
});
