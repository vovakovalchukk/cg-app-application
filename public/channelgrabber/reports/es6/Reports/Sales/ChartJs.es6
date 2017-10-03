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

            // set the newly fetched datasets
            this._resetDatasetMap();
            this._buildDataSets(data, dataType);
            this.chart.data.datasets = this.datasets;

            // change the date unit display format accordingly
            let dateUnit = data.dateUnit ? data.dateUnit : 'month';
            this._updateTimeAxisOptions(data.dateUnit);

            // update the actual chart
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
                                    day: 'D MMM YY',
                                    week: '[w/c] D MMM YY',
                                    month: 'MMM YY',
                                    year: 'YYYY'
                                },
                                minUnit: 'day',
                                unit: 'month',
                                isoWeekday: true
                            },
                            ticks: {
                                source: 'data'
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
            $.each(data.series, function (key, series) {
                this._addData(series.name, series.values, dataType);
            }.bind(this));
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

        _updateTimeAxisOptions(dateUnit) {
            this.chart.options.scales.xAxes[0].time.unit = dateUnit;
            this.chart.options.scales.xAxes[0].time.tooltipFormat = this.chart.options.scales.xAxes[0].time.displayFormats[dateUnit];
        }
    }

    return new ChartJs();
});
