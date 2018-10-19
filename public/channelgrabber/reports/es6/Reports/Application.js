import SalesService from 'Reports/Sales/Service';
import showHideFilters from 'showHideFilters';

class Application {
    constructor() {
        this.salesService = SalesService;
        
        this.buildSalesChart = this.buildSalesChart.bind(this);
        showHideFilters();
        
        $(document).on('showHideFilters-triggered', function() {
            $('#sub-header').toggle(50);
        });
    }
    
    buildSalesChart() {
        this.salesService.updateChart();
    }
}

export default Application;