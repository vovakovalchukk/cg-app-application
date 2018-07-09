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
                onSaveAndList: null,
                showVAT: true,
                massUnit: null,
                lengthUnit: null
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
        submitAndList: function() {
            /** @TODO: make sure that the account selection popup is shown only after the product save is successful - will be handled by LIS-202*/
            this.refs.productForm.submit();
            /** @TODO: pass the product data to the callback after we successfully save the product */
            this.props.onSaveAndList({id: 123});
        },
        onCancelClick: function() {
            this.props.resetCreateProducts();
            this.props.onCreateProductClose();
        },
        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup"
                    onYesButtonPressed={this.handleContainerSubmit}
                    onNoButtonPressed={this.onCancelClick}
                    closeOnYes={false}
                    headerText={"Create New Product"}
                    yesButtonText="Create Product"
                    noButtonText="Cancel"
                    contentClassNames={'container-content--can-extend-horizontal'}
                    wrapperClassNames={'container-wrapper--can-extend-horizontal'}
                >
                    <CreateProductForm
                        onSubmit={this.handleSubmit}
                        showVAT={this.props.showVAT}
                        massUnit={this.props.massUnit}
                        lengthUnit={this.props.lengthUnit}
                    />
                    {this.renderSaveAndListButtion()}
                </Container>
            );
        }
    });

    return CreateProduct;
});
