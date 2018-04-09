define([
    'react',
    'Common/Components/Container',
    'redux-form',
    'Product/Components/CreateProduct/CreateProductForm'



], function(
    React,
    Container,
    reduxForm,
    CreateProductForm

) {
    "use strict";

    var Field = reduxForm.Field;





    var CreateProduct = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose : function(){

                }
            }
        },
        getInitialState: function() {
            return {

            }
        },
        componentDidMount: function() {

        },

        submitFormData: function(){

        },

        handleSubmit : function(values){
            console.log('in handleSubmit with values: ', values);
            console.log(`Title: ${values.title}`);
        },



        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup "
                    onYesButtonPressed={this.handleSubmit}
                    onNoButtonPressed={this.props.onCreateProductClose}
                    closeOnYes={false}
                    headerText={"Create New Product"}
                    subHeaderText={"ChannelGrabber needs additional information to create a new product. Please check below and complete all the fields necessary."}
                    yesButtonText="Create Product"
                    noButtonText="Cancel"
                >

                    <CreateProductForm onSubmit={this.handleSubmit}/>


                </Container>
            );
        }
    });

    return CreateProduct;
});
