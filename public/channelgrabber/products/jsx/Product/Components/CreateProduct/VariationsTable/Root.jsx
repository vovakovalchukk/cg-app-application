import Redux from 'redux';
import ReactRedux from 'react-redux';
import ReduxForm from 'redux-form';
import stateFilters from 'Product/Components/CreateProduct/functions/stateFilters';
import Component from './Component';
import ActionCreators from './ActionCreators';
    

    const formName = 'createProductForm';

    const mapStateToProps = function(state) {
        var variationValues, attributeValues  = null;
        if (state.form.createProductForm.values) {
            variationValues = state.form[formName].values.variations;
            attributeValues = state.form[formName].values.attributes;
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
    export default Connector(Component);

