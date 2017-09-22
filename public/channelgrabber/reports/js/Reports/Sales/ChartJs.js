define([
    'Reports/OrderCounts/Response'
], function(
    Response
) {
    class ChartJs {
        constructor() {
            this.CANVAS_SELECTOR = '#salesChart';
            this.datasets = [];
            this._resetDatasetMap();
            this.init();
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

            this._resetDatasetMap();
            this._buildDataSets(data);
            this.chart.data.datasets = this.datasets;
            this.chart.update();
        }

        changeDatasetVisibility(datasetKey, visible) {
            let key = this._findDataSetByKey(datasetKey);
            if (key === false) {
                return false;
            }

            this.chart.data.datasets[key].hidden = !visible;
            this.chart.update();
        }

        _resetDatasetMap() {
            this.datasetsMap = {};
        }

        _findDataSetByKey(key) {
            if (this.datasetsMap[key] !== undefined) {
                return this.datasetsMap[key];
            }
            return false;
        }

        _getDefaultOptions() {
            return {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
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
                            time: {
                                displayFormats: {
                                    day: 'll'
                                }
                            }
                        }],
                        yAxes: [{
                            type: 'linear',
                        }]
                    },
                    legend: {
                        display: false
                    },
                    title: {
                        display: 'true',
                        text: 'Orders'
                    }
                }
            };
        }

        _buildDataSets(data) {
            this.datasets = [];
            this._buildSimpleKeysData(data);
            this._buildObjectKeysData(data);
        }

        _buildSimpleKeysData(data) {
            let allowedKeys = Response.allowed.keys;
            for (let i = 0; i < allowedKeys.length; i++) {
                if (data[allowedKeys[i]]) {
                    this.datasetsMap[allowedKeys[i]] = this.datasets.length;
                    this.datasets.push({
                        label: allowedKeys[i],
                        data: this._transformDataForChart(data[allowedKeys[i]]),
                        borderColor: 'blue',
                        fill: false
                    });
                }
            }
        }

        _buildObjectKeysData(data) {
            let allowedKeys = Response.allowed.objectKeys;
            for (let i = 0; i < allowedKeys.length; i++) {
                if (data[allowedKeys[i]]) {
                    $.each(data[allowedKeys[i]], (function (key, value) {
                        this.datasetsMap[key] = this.datasets.length;
                        this.datasets.push({
                            label: key,
                            data: this._transformDataForChart(value),
                            borderColor: 'red',
                            fill: false
                        });
                    }).bind(this));
                }
            }
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
