import Redux from 'redux';
import ReactRedux from 'react-redux';
import Component from 'Product/Components/StockModeInputs';
    
    const mapStateToProps = function(state) {
        return {
            stockModeOptions: state.account.stockModeOptions
        }
    };

    var Connector = ReactRedux.connect(mapStateToProps);
    export default Connector(Component);
