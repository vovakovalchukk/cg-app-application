define([
    'Reports/Sales/Service'
], function(
    SalesService
) {
    class Application {
        constructor() {
            this.salesService = SalesService;

            this.buildSalesChart = this.buildSalesChart.bind(this);
        }

        buildSalesChart() {
            this.salesService.updateChart();
        }
    }

    return Application;
});
