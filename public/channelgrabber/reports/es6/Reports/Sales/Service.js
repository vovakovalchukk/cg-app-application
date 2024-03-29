define([
    'Reports/Sales/ChartJs',
    'Reports/OrderCounts/Ajax'
], function(
    ChartJs,
    Ajax
) {
    class Service {
        constructor() {
            this.MAX_DATA_POINTS = 100;

            this.chart = ChartJs;
            this.ajax = Ajax;

            this.data = {};
            this.requestData = {};

            this.selectors = {
                channelFilterInput: ".channel-filter input[type='checkbox']",
                dataTypeFilter: "input[name='data-type']",
                applyFiltersButton: "#filters input[data-action='apply-filters']",
                filterInputs: "#filters :input[name]",
                channelFilterContainer: ".channel-filter",
                spinner: ".spinner-container"
            };

            this.loadEventListeners();

            this.updateChart = this.updateChart.bind(this);
            this.redrawChartFromAjax = this.redrawChartFromAjax.bind(this);
            this.handleAjaxError = this.handleAjaxError.bind(this);
        }

        loadEventListeners() {
            $(this.selectors.applyFiltersButton).on("click", (function () {
                this.updateChart();
            }).bind(this));

            $(this.selectors.channelFilterInput).on("click", (function (e) {
                let $object = $(e.currentTarget);
                let datasetKey = $object.attr('name');
                let visible = $object.is(':checked');
                this.changeDatasetVisibility(datasetKey, visible);
            }).bind(this));

            $(this.selectors.dataTypeFilter).on("change", (function() {
                this._redrawChart();
            }).bind(this));
        }

        updateChart() {
            this._showSpinner();
            this.buildRequestData();
            this._updateChartData();
        }

        redrawChartFromAjax(data) {
            this.data = data;
            this._redrawChart();
            this._hideSpinner();
        }

        handleAjaxError() {
            this._hideSpinner();
        }

        buildRequestData() {
            this._resetRequestData();
            this.buildFiltersRequestData();
        }

        buildFiltersRequestData() {
            $(this.selectors.filterInputs).each(function(index, input) {
                let $input = $(input);
                let value = $.trim($input.val());
                if (!value.length) {
                    return;
                }
                let name = $input.attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
                if (name.indexOf('[]') >= 0) {
                    name = name.replace('[]', '');
                    if (!Array.isArray(this.requestData[name])) {
                        this.requestData[name] = [];
                    }
                    this.requestData[name].push(value);
                } else {
                    this.requestData[name] = value;
                }
            }.bind(this));
        }

        changeDatasetVisibility(datasetKey, visibility) {
            return this.chart.changeDatasetVisibility(datasetKey, visibility);
        }

        resetFilters() {
            $(this.selectors.channelFilterInput).prop( "checked", true);
        }

        updateFilters() {
            this.hideFilters();
            $(this.selectors.channelFilterInput).each(function(key, element) {
                let $element = $(element);
                let $container = $element.closest('.channel-filter');
                let colour = this.chart.getColorByDatasetKey($element.attr('name'));
                if (!colour) {
                    return;
                }
                $container.find('span.logo').css('color', colour);
                $container.show();
            }.bind(this));
        }

        hideFilters() {
            $(this.selectors.channelFilterContainer).each(function(key, element) {
                $(element).hide();
            });
        }

        _updateChartData() {
            this.ajax.fetch(this.requestData, this.redrawChartFromAjax, this.handleAjaxError);
        }

        _redrawChart() {
            this.chart.update(this.data, this._getDataType());
            this.resetFilters();
            this.updateFilters();
        }

        _getDataType() {
            return $(this.selectors.dataTypeFilter + ':checked').data('type');
        }

        _resetRequestData() {
            this.requestData = {
                'dimension': 'channel',
                'metrics': ['count', 'orderValue'],
                'limit': this.MAX_DATA_POINTS
            };
        }

        _showSpinner() {
            $(this.selectors.spinner).show();
        }

        _hideSpinner() {
            $(this.selectors.spinner).hide();
        }
    }

    return new Service();
});
