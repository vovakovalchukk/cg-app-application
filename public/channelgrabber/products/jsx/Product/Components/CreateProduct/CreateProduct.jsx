define([
    'react',
    'Common/Components/Container',
    'redux-form',
    'Product/Components/CreateProduct/CreateProductForm'
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
                onCreateProductClose: null
            };
        },
        handleContainerSubmit: function () {
            this.refs.productForm.submit();
        },
        handleSubmit: function (values) {
            console.log('in handleSubmit with values: ', values);
            console.log(`Title: ${values.title}`);
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
                    subHeaderText={"ChannelGrabber needs additional information to create a new product. Please check below and complete all the fields necessary."}
                    yesButtonText="Create Product"
                    noButtonText="Cancel"
                >
                    
                    <CreateProductForm onSubmit={this.handleSubmit} ref="productForm"/>
                </Container>
            );
        }
    });
    
    return CreateProduct;
});
