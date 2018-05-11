define([
    'redux',
    'react-redux',
    'redux-form',
    'Product/Components/CreateProduct/Form/Form',
    'Product/Components/CreateProduct/Form/FormActionCreators',
], function (
    Redux,
    ReactRedux,
    ReduxForm,
    CreateProductForm,
    formActionCreators
) {
    var resetSection = ReduxForm.resetSection;
    var untouch = ReduxForm.untouch;
    var change = ReduxForm.change;
    var unregister = ReduxForm.unregisterField;

    "use strict";
    const mapStateToProps = function(state){
        return{
            uploadedImages: state.uploadedImages,
            taxRates:state.account.taxRates,
            variationRowProperties:state.variationRowProperties
        }
    };
    const mapDispatchToProps = function(dispatch) {
        var combinedActionCreators = Object.assign({}, formActionCreators, {
            resetSection: resetSection,
            untouch:untouch,
            change:change,
            unregister:unregister
        });
        return Redux.bindActionCreators(combinedActionCreators, dispatch);
    };

    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return FormConnector(CreateProductForm);
});