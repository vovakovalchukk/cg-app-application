define([
    'redux',
    'react-redux',
    'redux-form',
    'Product/Components/CreateProduct/functions/stateFilters',
    './Component',
    './ActionCreators'
], function(
    Redux,
    ReactRedux,
    ReduxForm,
    stateFilters,
    Component,
    ActionCreators
) {
    "use strict";
    const mapStateToProps = function(state) {
        return {
            variationsTable: stateFilters.filterFields(1, state.variationsTable),
            uploadedImages: state.uploadedImages,
            stockModeOptions: state.account.stockModeOptions,
            variationValues:state.form.createProductForm.values.variations,

        }
    };

    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };

    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);

});