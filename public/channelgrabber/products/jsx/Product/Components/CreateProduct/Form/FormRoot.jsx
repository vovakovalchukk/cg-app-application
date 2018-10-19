import Redux from 'redux';
import ReactRedux from 'react-redux';
import ReduxForm from 'redux-form';
import CreateProductForm from 'Product/Components/CreateProduct/Form/Form';
import formActionCreators from 'Product/Components/CreateProduct/Form/FormActionCreators';
    
    var resetSection = ReduxForm.resetSection;
    var untouch = ReduxForm.untouch;
    var change = ReduxForm.change;
    var unregister = ReduxForm.unregisterField;
    var initialize = ReduxForm.initialize;

    const mapStateToProps = function(state) {
        return {
            uploadedImages: state.uploadedImages,
            taxRates: state.account.taxRates,
            variationRowProperties: state.variationRowProperties,
            formValues: ReduxForm.getFormValues('createProductForm')(state)
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
        return Redux.bindActionCreators(combinedActionCreators, dispatch);
    };

    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    export default FormConnector(CreateProductForm);
