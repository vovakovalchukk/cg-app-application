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
                onCreateProductClose: null
            };
        },
        handleContainerSubmit: function() {
            this.refs.productForm.submit();
        },
        handleSubmit: function(values) {
            if (values.productImage) this.postImageDataToApi(values.productImage.binaryDataString);
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
                        ref="productForm"
                    />

                </Container>
            );
        }
    });


    function filterFields(variationsTable, tablesFields){
        console.log('in removeDImensionsFields with variationsFields: ', variationsTable , ' and tablesFIelds: ', tablesFields);
        var fieldsToAllow = variationsTable.tablesFields.filter(function(tableField){
            if(tableField.tableId == 1) {
                return tableField.fieldId;
            }
        });
        var fieldIdsToAllow =[];
        for(var i=0;i<fieldsToAllow.length;i++){
            fieldIdsToAllow.push(fieldsToAllow[i].fieldId)
        }
        console.log('fieldIdsToAllowIds ', fieldIdsToAllow);

        var filteredFields= variationsTable.fields.filter(function(field){
            console.log('in filter and field.id =', field.id , ' and fieldIdsToAllow: ' , fieldIdsToAllow)
            return fieldIdsToAllow.indexOf(field.id) > -1;
        });
        console.log('filteredFIelds: ' , filteredFields)
        var newState = Object.assign({},variationsTable,{
            fields : filteredFields
        });
        return newState;
    }

    return CreateProduct;
});
