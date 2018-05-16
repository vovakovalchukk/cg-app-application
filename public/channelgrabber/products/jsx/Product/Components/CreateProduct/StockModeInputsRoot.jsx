define([
    'redux',
    'react-redux',
    'Product/Components/StockModeInputs'
], function(
    Redux,
    ReactRedux,
    Component
) {
    "use strict";
    const mapStateToProps = function(state) {
        return {
            stockModeOptions: state.account.stockModeOptions
        }
    };

    var Connector = ReactRedux.connect(mapStateToProps);
    return Connector(Component);
})