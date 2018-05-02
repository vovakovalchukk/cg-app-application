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
            resetSection: resetSection
        });
        return Redux.bindActionCreators(combinedActionCreators, dispatch);
    };
    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return FormConnector(CreateProductForm);
})