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

    const mapStateToProps = function(state){
        return{
            images: state.images
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(formActionCreators, dispatch);
    };

    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return FormConnector(CreateProductForm);
})