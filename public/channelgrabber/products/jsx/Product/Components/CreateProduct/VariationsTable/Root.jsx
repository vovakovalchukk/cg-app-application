import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
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
        return bindActionCreators(ActionCreators, dispatch);
    };

    var Connector = connect(mapStateToProps, mapDispatchToProps);
    export default Connector(Component);

