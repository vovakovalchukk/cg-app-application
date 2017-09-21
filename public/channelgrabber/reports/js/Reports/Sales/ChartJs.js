define([], function() {

    class ChartJs {
        constructor() {
            this.CANVAS_SELECTOR = '#salesChart';
        }

        create(data) {
            let options = this._getDefaultOptions();
            options.data.datasets = this._buildDataSets(data);
            this.chart = new Chart(
                $(this.CANVAS_SELECTOR),
                options
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
                    scales: {
                        xAxes: [{
                            type: 'time',
                            bonds: 'data',
                            distribution: 'linear',
                            ticks: {
                                source: 'data'
                            },
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'll'
                                }
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            };
        }

        _buildDataSets(data) {
            return [{
                    label: 'ebay',
                    data: data,
                    borderColor: 'blue',
                    fill: false
                }];
        }
    }

    return new ChartJs();
});
