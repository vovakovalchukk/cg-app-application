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
        console.log('in mapStateToProps with state ' , state);
        return {
            variationsTable: state.variationsTable,
            uploadedImages: state.uploadedImages,
            stockModeOptions: state.account.stockModeOptions,
            formVariationValues: getVariationFormValues(state),
        }
    };

    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };

    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);

    function getVariationFormValues(state) {
        var formValues = ReduxForm.getFormValues('createProductForm')(state);
        if (!formValues) return null;
        var variationFormValues = ReduxForm.getFormValues('createProductForm')(state).variations;
        if (!variationFormValues) return null;
        return variationFormValues
    }

})