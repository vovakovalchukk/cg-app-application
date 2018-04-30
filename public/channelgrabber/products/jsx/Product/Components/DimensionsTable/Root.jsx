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
        console.log('in mapStateToProps with state: ' , state);
        var filteredState = stateFilters.filterFields(2, state.variationsTable);

        console.log('in dimensionsRoot with state ', state);
        return {
            fields: filteredState.fields,
            rows: state.variationsTable.variations,
            values: state.form.createProductForm.values,
            uploadedImages: state.uploadedImages,
        }
    };

    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };
    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);


});