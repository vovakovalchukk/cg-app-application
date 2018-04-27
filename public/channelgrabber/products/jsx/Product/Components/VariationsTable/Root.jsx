define([
    'redux',
    'react-redux',
    'redux-form',
    './Component',
    './ActionCreators'
], function(
    Redux,
    ReactRedux,
    ReduxForm,
    Component,
    ActionCreators
) {
    "use strict";
    const mapStateToProps = function(state) {
        return {
            variationsTable: state.variationsTable,
            uploadedImages: state.uploadedImages,
            stockModeOptions: state.account.stockModeOptions,
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };
    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);
});