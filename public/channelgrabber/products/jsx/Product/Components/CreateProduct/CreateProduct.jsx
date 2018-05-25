define([
    'react',
    'Common/Components/Container',
    'redux-form',
    'Product/Components/CreateProduct/Form/FormRoot'
], function(
    React,
    Container,
    reduxForm,
    CreateProductForm
) {
    "use strict";

    var CreateProduct = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose: null,
                submitFormDispatch: null,
                onSaveAndList: null
            };
        },
        handleContainerSubmit: function() {
            this.props.formContainerSubmitClick();
        },
        handleSubmit: function(values) {
            this.props.formSubmit(values, this.props.redirectToProducts);
        },
        renderSaveAndListButtion: function() {
            /**
             * @TODO: this button has no styling yet, it's just floating around. We need to sort it out after (will be handled by LIS-202)
             * this component will be fully implemented.
             * */
            return null;
            return (<div className="button container-btn yes" onClick={this.submitAndList}>Save and list</div>);
        },
        submitAndList: function () {
            /** @TODO: make sure that the account selection popup is shown only after the product save is successful - will be handled by LIS-202*/
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
                    yesButtonText="Create Product"
                    noButtonText="Cancel"
                >
                    <CreateProductForm
                        onSubmit={this.handleSubmit}
                    />
                    {this.renderSaveAndListButtion()}
                </Container>
            );
        }
    });

    return CreateProduct;
});
