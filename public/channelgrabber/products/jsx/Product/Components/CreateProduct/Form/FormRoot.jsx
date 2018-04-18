define([
    'redux',
    'react-redux',
    'Product/Components/CreateProduct/Form/Form',
    'Product/Components/CreateProduct/Form/FormActionCreators',
], function (
    Redux,
    ReactRedux,
    CreateProductForm,
    formActionCreators
) {
    "use strict";
    const mapStateToProps = function(state){
        return{
            uploadedImages: state.uploadedImages,
            taxRates:state.account.taxRates
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(formActionCreators, dispatch);
    };
    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return FormConnector(CreateProductForm);
})