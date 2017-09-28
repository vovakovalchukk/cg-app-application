define([
    'Reports/OrderCounts/Response',
    'ChartJs'
], function(
    Response,
    Chart
) {
    class ChartJs {
        constructor() {
            this.CANVAS_SELECTOR = '#salesChart';
            this.datasets = [];
            this._resetDatasetMap();
            this._buildColourMap();
            this._init();
        }

        _init() {
            this.chart = new Chart(
                $(this.CANVAS_SELECTOR),
                this._getDefaultOptions()
            );
        }

        update(data, dataType) {
            this.data = data;
            if (!this.chart) {
                return false;
            }

            this._resetDatasetMap();
            this._buildDataSets(data, dataType);
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
                    hover: {
                        intersect: false,
                        mode: 'x'
                    },
                    tooltips: {
                        mode: 'x',
                        intersect: false
                    },
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

        _buildDataSets(data, dataType) {
            this.datasets = [];
            this._buildSimpleKeysData(data, dataType);
            this._buildObjectKeysData(data, dataType);
        }

        _buildSimpleKeysData(data, dataType) {
            let allowedKeys = Response.allowed.keys;
            for (let i = 0; i < allowedKeys.length; i++) {
                if (data[allowedKeys[i]]) {
                    this._addData(
                        allowedKeys[i],
                        data[allowedKeys[i]],
                        dataType
                    );
                }
            }
        }

        _buildObjectKeysData(data, dataType) {
            let allowedKeys = Response.allowed.objectKeys;
            for (let i = 0; i < allowedKeys.length; i++) {
                if (data[allowedKeys[i]]) {
                    $.each(data[allowedKeys[i]], (function (key, value) {
                        this._addData(
                            key,
                            value,
                            dataType
                        );
                    }).bind(this));
                }
            }
        }

        _addData(label, data, dataType) {
            this.datasetsMap[label] = this.datasets.length;
            this._addDataToDataset(
                label,
                this._transformDataForChart(data, dataType),
                this.getColourByIndex(this.datasets.length)
            );
        }

        _addDataToDataset(label, data, color) {
            this.datasets.push({
                pointRadius: 1,
                label: label,
                data: data,
                borderColor: color,
                fill: false,
                lineTension: 0.25
            });
        }

        _transformDataForChart(data, dataType) {
            let result = [];
            $.each(data, function(key, value) {
                result.push({
                    'x': key,
                    'y': value[dataType],
                });
            });
            return result;
        }
    }

    return new ChartJs();
});
