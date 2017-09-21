define([
    'Reports/OrderCounts/Response'
], function(
    Response
) {
    class ChartJs {
        constructor() {
            this.CANVAS_SELECTOR = '#salesChart';
            this.init();

            this.colours = [

            ];
        }

        init() {
            this.chart = new Chart(
                $(this.CANVAS_SELECTOR),
                this._getDefaultOptions()
            );
        }

        update(data) {
            if (!this.chart) {
                return false;
            }

            this.chart.data.datasets = this._buildDataSets(data);
            this.chart.update();
        }

        _getDefaultOptions() {
            return {
                type: 'line',
                data: {},
                options: {
                    elements: {
                        line: {
                            tension: 0, // disables bezier curves
                        }
                    },
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            type: 'time',
                            bonds: 'data',
                            distribution: 'linear',
                            autoSkip: true,
                            ticks: {
                                source: 'data'
                            },
                            time: {
                                unit: 'day',
                                unitStepSize: 100,
                                displayFormats: {
                                    day: 'll'
                                }
                            }
                        }],
                        yAxes: [{
                            type: 'linear',
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        position: 'left',
                        labels: {
                            usePointStyle: true
                        }
                    },
                    title: {
                        display: 'true',
                        text: 'Orders'
                    }
                }
            };
        }

        _buildDataSets(data) {
            let datasets = [];
            let allowedKeys = Response.allowed.keys;
            for (let i = 0; i < allowedKeys.length; i++) {
                if (data[allowedKeys[i]]) {
                    datasets.push({
                        label: allowedKeys[i],
                        data: this._transformDataForChart(data[allowedKeys[i]]),
                        borderColor: 'blue',
                        fill: false
                    });
                }
            }

            allowedKeys = Response.allowed.objectKeys;
            for (let i = 0; i < allowedKeys.length; i++) {
                if (data[allowedKeys[i]]) {
                    $.each(data[allowedKeys[i]], (function (key, value) {
                        datasets.push({
                            label: key,
                            data: this._transformDataForChart(value),
                            borderColor: 'red',
                            fill: false
                        });
                    }).bind(this));
                }
            }

            return datasets;
        }

        _transformDataForChart(data) {
            let result = [];
            $.each(data, function(key, value) {
                result.push({
                    'x': key,
                    'y': value,
                });
            });
            return result;
        }
    }

    return new ChartJs();
});
