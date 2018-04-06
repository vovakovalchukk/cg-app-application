define([
    'react',
    'Common/Components/Container'

], function(
    React,
    Container

) {
    "use strict";

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

        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup "
                    onYesButtonPressed={this.submitFormData}
                    onNoButtonPressed={this.props.onCreateProductClose}
                    closeOnYes={false}
                    headerText={"Create New Product"}
                    subHeaderText={"ChannelGrabber needs additional information to create a new product. Please check below and complete all the fields necessary."}
                    yesButtonText="Create Product"
                    noButtonText="Cancel"
                >


                </Container>
            );
        }
    });

    return CreateProduct;
});
