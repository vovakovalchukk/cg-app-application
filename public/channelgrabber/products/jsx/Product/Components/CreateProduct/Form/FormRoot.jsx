import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import {resetSection, untouch, change, unregister, initialize, getFormValues} from 'redux-form';
import CreateProductForm from 'Product/Components/CreateProduct/Form/Form';
import formActionCreators from 'Product/Components/CreateProduct/Form/FormActionCreators';

    const mapStateToProps = function(state) {
        return {
            uploadedImages: state.uploadedImages,
            taxRates: state.account.taxRates,
            variationRowProperties: state.variationRowProperties,
            formValues: getFormValues('createProductForm')(state)
        }
    };
    const mapDispatchToProps = function(dispatch) {
        var combinedActionCreators = Object.assign({}, formActionCreators, {
            resetSection: resetSection,
            untouch: untouch,
            change: change,
            unregister: unregister,
            initialize: initialize
        });
        return bindActionCreators(combinedActionCreators, dispatch);
    };

    var FormConnector = connect(mapStateToProps, mapDispatchToProps);
    export default FormConnector(CreateProductForm);
