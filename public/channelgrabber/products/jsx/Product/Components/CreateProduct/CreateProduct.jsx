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
    var submitForm = reduxForm.submit;

    var CreateProduct = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose: null,
                submitFormDispatch:null
            };
        },
        handleContainerSubmit: function() {
            console.log('in handleContainerSubmitClick');
            this.props.formContainerSubmitClick();
        },
        handleSubmit:function(values,dispatch,props){
            console.log('in handleSubmit with values: ' , values , ' dispatch : ' , dispatch , ' and props : ' , props);
            this.props.formSubmit(values);
        },
        render: function() {
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
                </Container>
            );
        }
    });

    return CreateProduct;
});
