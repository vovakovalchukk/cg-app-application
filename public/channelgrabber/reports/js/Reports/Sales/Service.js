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

            this.createChart = this.createChart.bind(this);
            this._buildChart = this._buildChart.bind(this);
            this._redrawChart = this._redrawChart.bind(this);
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
                    this._redrawChart(data);
                }).bind(this));
            }).bind(this));
        }

        createChart() {
            this.ajax.fetch([], this._buildChart);
        }

        _buildChart(data) {
            this.chart.create(data);
        }

        _redrawChart(data) {
            this.chart.update(data);
        }
    }

    return new Service();
});
