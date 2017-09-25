'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

define(['Reports/OrderCounts/Response'], function (Response) {
    var ChartJs = function () {
        function ChartJs() {
            _classCallCheck(this, ChartJs);

            this.CANVAS_SELECTOR = '#salesChart';
            this.datasets = [];
            this._resetDatasetMap();
            this._buildColourMap();
            this._init();
        }

        _createClass(ChartJs, [{
            key: '_init',
            value: function _init() {
                this.chart = new Chart($(this.CANVAS_SELECTOR), this._getDefaultOptions());
            }
        }, {
            key: 'update',
            value: function update(data, dataType) {
                this.data = data;
                if (!this.chart) {
                    return false;
                }

                this._resetDatasetMap();
                this._buildDataSets(data, dataType);
                this.chart.data.datasets = this.datasets;
                this.chart.update();
            }
        }, {
            key: 'changeDatasetVisibility',
            value: function changeDatasetVisibility(datasetKey, visible) {
                var key = this._findDataSetByKey(datasetKey);
                if (key === false) {
                    return false;
                }

                this.chart.data.datasets[key].hidden = !visible;
                this.chart.update();
            }
        }, {
            key: 'getColourByIndex',
            value: function getColourByIndex(index) {
                return this.colourMap[index % this.colourMap.length];
            }
        }, {
            key: 'getColorByDatasetKey',
            value: function getColorByDatasetKey(datasetKey) {
                var key = this._findDataSetByKey(datasetKey);
                if (key === false) {
                    return false;
                }

                return this.getColourByIndex(key);
            }
        }, {
            key: '_buildColourMap',
            value: function _buildColourMap() {
                this.colourMap = ['steelblue', 'darkgoldenrod', 'green', 'brown', 'red', 'black', 'orange'];
            }
        }, {
            key: '_resetDatasetMap',
            value: function _resetDatasetMap() {
                this.datasetsMap = {};
            }
        }, {
            key: '_findDataSetByKey',
            value: function _findDataSetByKey(key) {
                if (this.datasetsMap[key] !== undefined) {
                    return this.datasetsMap[key];
                }
                return false;
            }
        }, {
            key: '_getDefaultOptions',
            value: function _getDefaultOptions() {
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
                                afterBuildTicks: function afterBuildTicks(axis) {
                                    if (!axis.ticks) {
                                        return;
                                    }

                                    $.each(axis.ticks, function (key, value) {
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
        }, {
            key: '_buildDataSets',
            value: function _buildDataSets(data, dataType) {
                this.datasets = [];
                this._buildSimpleKeysData(data, dataType);
                this._buildObjectKeysData(data, dataType);
            }
        }, {
            key: '_buildSimpleKeysData',
            value: function _buildSimpleKeysData(data, dataType) {
                var allowedKeys = Response.allowed.keys;
                for (var i = 0; i < allowedKeys.length; i++) {
                    if (data[allowedKeys[i]]) {
                        var currentIndex = this.datasets.length;
                        this.datasetsMap[allowedKeys[i]] = currentIndex;
                        this.datasets.push({
                            label: allowedKeys[i],
                            data: this._transformDataForChart(data[allowedKeys[i]], dataType),
                            borderColor: this.getColourByIndex(currentIndex),
                            fill: false
                        });
                    }
                }
            }
        }, {
            key: '_buildObjectKeysData',
            value: function _buildObjectKeysData(data, dataType) {
                var allowedKeys = Response.allowed.objectKeys;
                for (var i = 0; i < allowedKeys.length; i++) {
                    if (data[allowedKeys[i]]) {
                        $.each(data[allowedKeys[i]], function (key, value) {
                            var currentIndex = this.datasets.length;
                            this.datasetsMap[key] = currentIndex;
                            this.datasets.push({
                                label: key,
                                data: this._transformDataForChart(value, dataType),
                                borderColor: this.getColourByIndex(currentIndex),
                                fill: false
                            });
                        }.bind(this));
                    }
                }
            }
        }, {
            key: '_transformDataForChart',
            value: function _transformDataForChart(data, dataType) {
                var result = [];
                $.each(data, function (key, value) {
                    result.push({
                        'x': key,
                        'y': value[dataType]
                    });
                });
                return result;
            }
        }]);

        return ChartJs;
    }();

    return new ChartJs();
});
