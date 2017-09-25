'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

define(['Reports/Sales/ChartJs', 'Reports/OrderCounts/Ajax'], function (ChartJs, Ajax) {
    var Service = function () {
        function Service() {
            _classCallCheck(this, Service);

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

        _createClass(Service, [{
            key: 'loadEventListeners',
            value: function loadEventListeners() {
                $(this.selectors.applyFiltersButton).on("click", function () {
                    this.updateChart();
                }.bind(this));

                $(this.selectors.channelFilterInput).on("click", function (e) {
                    var $object = $(e.currentTarget);
                    var datasetKey = $object.attr('name');
                    var visible = $object.is(':checked');
                    this.changeDatasetVisibility(datasetKey, visible);
                }.bind(this));

                $(this.selectors.dataTypeFilter).on("change", function () {
                    this._redrawChart();
                }.bind(this));
            }
        }, {
            key: 'updateChart',
            value: function updateChart() {
                this._showSpinner();
                this.ajax.fetch(this.buildRequestData(), this.redrawChartFromAjax, this.handleAjaxError);
            }
        }, {
            key: 'redrawChartFromAjax',
            value: function redrawChartFromAjax(data) {
                this.data = data;
                this._redrawChart();
                this._hideSpinner();
            }
        }, {
            key: 'handleAjaxError',
            value: function handleAjaxError() {
                this._hideSpinner();
            }
        }, {
            key: 'buildRequestData',
            value: function buildRequestData() {
                this._resetRequestData();
                this.buildFiltersRequestData();
                return this.requestData;
            }
        }, {
            key: 'buildFiltersRequestData',
            value: function buildFiltersRequestData() {
                $(this.selectors.filterInputs).each(function (index, input) {
                    var $input = $(input);
                    var value = $.trim($input.val());
                    if (!value.length) {
                        return;
                    }
                    var name = $input.attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
                    this.requestData[name] = value;
                }.bind(this));
            }
        }, {
            key: 'changeDatasetVisibility',
            value: function changeDatasetVisibility(datasetKey, visibility) {
                return this.chart.changeDatasetVisibility(datasetKey, visibility);
            }
        }, {
            key: 'resetFilters',
            value: function resetFilters() {
                $(this.selectors.channelFilterInput).prop("checked", true);
            }
        }, {
            key: 'updateFilters',
            value: function updateFilters() {
                this.hideFilters();
                $(this.selectors.channelFilterInput).each(function (key, element) {
                    var $element = $(element);
                    var $container = $element.closest('.channel-filter');
                    var colour = this.chart.getColorByDatasetKey($element.attr('name'));
                    if (!colour) {
                        return;
                    }
                    $container.find('span.logo').css('color', colour);
                    $container.show();
                }.bind(this));
            }
        }, {
            key: 'hideFilters',
            value: function hideFilters() {
                $(this.selectors.channelFilterContainer).each(function (key, element) {
                    $(element).hide();
                });
            }
        }, {
            key: '_redrawChart',
            value: function _redrawChart() {
                this.chart.update(this.data, this._getDataType());
                this.resetFilters();
                this.updateFilters();
            }
        }, {
            key: '_getDataType',
            value: function _getDataType() {
                return $(this.selectors.dataTypeFilter + ':checked').data('type');
            }
        }, {
            key: '_resetRequestData',
            value: function _resetRequestData() {
                this.requestData = {
                    'strategy': ['channel', 'total'],
                    'strategyType': ['count', 'orderValue'],
                    'unitType': 'day'
                };
            }
        }, {
            key: '_showSpinner',
            value: function _showSpinner() {
                $(this.selectors.spinner).show();
            }
        }, {
            key: '_hideSpinner',
            value: function _hideSpinner() {
                $(this.selectors.spinner).hide();
            }
        }]);

        return Service;
    }();

    return new Service();
});
