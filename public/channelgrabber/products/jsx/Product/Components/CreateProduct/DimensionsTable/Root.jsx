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
    const mapStateToProps = function(state, ownProps) {
        var filteredState = stateFilters.filterFields(2, state.variationsTable);
        return {
            fields: filteredState.fields,
            rows: state.variationsTable.variations,
            values: state.form.createProductForm.values,
            uploadedImages: state.uploadedImages,
            classNames: ownProps.classNames,
            cells: state.variationsTable.cells
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };
    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);
});