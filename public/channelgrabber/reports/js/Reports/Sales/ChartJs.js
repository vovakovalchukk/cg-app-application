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
            this._buildColourMap();
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

        getColourByIndex(index) {
            return this.colourMap[index % this.colourMap.length];
        }

        getColorByDatasetKey(datasetKey)
        {
            let key = this._findDataSetByKey(datasetKey);
            if (key === false) {
                return false;
            }

            return this.getColourByIndex(key);
        }

        _buildColourMap() {
            this.colourMap = [
                'steelblue',
                'darkgoldenrod',
                'green',
                'brown',
                'red',
                'black',
                'orange'
            ];
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
                            afterBuildTicks: function (axis) {
                                if (!axis.ticks) {
                                    return;
                                }

                                $.each(axis.ticks, function(key, value) {
                                    if (Number.isInteger(value) && value >= 0) {
                                        return;
                                    }
                                    delete axis.ticks[key];
                                });
                            }
                        }]
                    },
                    legend: {
                        display: false
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
                    let currentIndex = this.datasets.length;
                    this.datasetsMap[allowedKeys[i]] = currentIndex;
                    this.datasets.push({
                        label: allowedKeys[i],
                        data: this._transformDataForChart(data[allowedKeys[i]]),
                        borderColor: this.getColourByIndex(currentIndex),
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
                        let currentIndex = this.datasets.length;
                        this.datasetsMap[key] = currentIndex;
                        this.datasets.push({
                            label: key,
                            data: this._transformDataForChart(value),
                            borderColor: this.getColourByIndex(currentIndex),
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
