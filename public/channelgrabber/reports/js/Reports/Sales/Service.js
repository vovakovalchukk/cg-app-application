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
            $("#filters input[data-action='apply-filters']").on("click", (function() {
                let filters = {};
                $("#filters :input[name]").each(function() {
                    let value = $.trim($(this).val());
                    if (!value.length) {
                        return;
                    }
                    let name = $(this).attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
                    filters[name] = value;
                });
                this.ajax.fetch(filters, (function (data) {
                    this.redrawChart(data);
                }).bind(this));
            }).bind(this));
        }

        updateChart() {
            this.ajax.fetch([], this.redrawChart);
        }

        redrawChart(data) {
            this.chart.update(data);
        }
    }

    return new Service();
});
