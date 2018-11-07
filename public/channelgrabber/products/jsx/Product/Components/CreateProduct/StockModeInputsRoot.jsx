import {connect} from 'react-redux';
import Component from 'Product/Components/StockModeInputs';
    
    const mapStateToProps = function(state) {
        return {
            stockModeOptions: state.account.stockModeOptions
        }
    };

    var Connector = connect(mapStateToProps);
    export default Connector(Component);
