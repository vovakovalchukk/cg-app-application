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
                onCreateProductClose: null,
                onSaveAndList: null
            };
        },
        handleContainerSubmit: function () {
            this.refs.productForm.submit();
        },
        handleSubmit: function (values) {
            console.log('in handleSubmit with values: ', values);
            console.log(`Title: ${values.title}`);
        },
        renderSaveAndListButtion: function() {
            /**
             * @TODO: this button has no styling yet, it's just floating around. We need to sort it out after
             * this component will be fully implemented.
             * */
            return (<div className="button container-btn yes" onClick={this.submitAndList}>Save and list</div>);
        },
        submitAndList: function () {
            /** @TODO: make sure that the account selection popup is shown only after the product save is successful */
            this.refs.productForm.submit();
            /** @TODO: pass the product data to the callback after we successfully save the product */
            this.props.onSaveAndList({id: 123});
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
                    {this.renderSaveAndListButtion()}
                </Container>
            );
        }
    });

    return CreateProduct;
});
