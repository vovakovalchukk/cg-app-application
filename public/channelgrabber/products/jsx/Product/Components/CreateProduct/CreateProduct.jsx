define([
    'react',
    'Common/Components/Container',
    'redux-form',
    'Product/Components/CreateProduct/Form/FormRoot'
], function (
    React,
    Container,
    reduxForm,
    CreateProductForm
) {
    "use strict";
    
    var CreateProduct = React.createClass({
        getDefaultProps: function () {
            return {
                onCreateProductClose: null,
                taxRates:null
            };
        },
        handleContainerSubmit: function () {
            this.refs.productForm.submit();
        },
        handleSubmit: function (values) {
            if(values.productImage) this.postImageDataToApi(values.productImage.binaryDataString);
        },
        render: function () {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup "
                    onYesButtonPressed={this.handleContainerSubmit}
                    onNoButtonPressed={this.props.onCreateProductClose}
                    closeOnYes={false}
                    headerText={"Create New Product"}
                    yesButtonText="Create Product"
                    noButtonText="Cancel"
                >
                    <CreateProductForm
                        onSubmit={this.handleSubmit}
                        ref="productForm"
                        taxRates={this.props.taxRates}
                    />

                </Container>
            );
        }
    });
    
    return CreateProduct;
});
