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

    const formName = 'createProductForm';

    const mapStateToProps = function(state) {
        var variationValues, attributeValues  = null;
        if (state.form.createProductForm.values) {
            variationValues = state.form[formName].values.variations,
            attributeValues = state.form[formName].values.attributes
        }
        return {
            variationsTable: stateFilters.filterFields(1, state.variationsTable),
            uploadedImages: state.uploadedImages,
            stockModeOptions: state.account.stockModeOptions,
            variationValues,
            attributeValues
        }
    };

    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };

    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);

});