define([
    'react',
    'redux-form',
    'Product/Components/CreateProduct/functions/utility',
    'Common/Components/ImageDropDown',
    'Common/Components/Select'

], function(
    React,
    reduxForm,
    utility,
    ImageDropDown,
    Select
) {

    var Field = reduxForm.Field;
    var Fields = reduxForm.Fields;
    var FormSection = reduxForm.FormSection;

    var DimensionsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                fields: [],
                rows: [{}],
                values: [],
                formSectionName: null
            };
        },
        renderHeadings: function() {

        },
        renderHeadings: function() {
            return this.props.fields.map(function(field) {
                return this.renderHeading(field);
            }.bind(this));
        },
        renderHeading: function(field) {
            var jsx = (
                <th className={'' +
                'c-table-wfunctionsith-inputs__cell ' +
                'c-table-with-inputs__cell-heading '
                }>{field.label}</th>
            );
            return jsx;
        },
        renderHeaderRow: function() {
            return (
                <FormSection name={this.props.formSectionName["headerRow"]}>
                    <tr>
                        {this.renderHeadings()}
                    </tr>
                </FormSection>
            );
        },
        renderImageDropdown: function(reduxFieldProps, variationId, uploadedImages) {
            return <ImageDropDown
                selected={utility.getUploadedImageById(reduxFieldProps.input.value, uploadedImages)}
                autoSelectFirst={false}
                images={uploadedImages}
            />
        },
        renderCustomSelect: function(field, reduxFormFieldProps) {
            return <Select
                options={field.options}
                autoSelectFirst={false}
                title={name}
                customOptions={true}
                selectedOption={{
                    name: reduxFormFieldProps.input.value,
                    value: reduxFormFieldProps.input.value
                }}
            />
        },
        fieldNoInputRenderMethods:{
            text: function(variationId, field, value) {
                console.log("in text input render with value : " , value)
                return (
                    <span>{value}</span>
                )
            },
            image: function(variationId, field,imageId) {
                var uploadedImages = this.props.uploadedImages.images;
                var imageUrl = '';
                for(var i=0; i<uploadedImages.length; i++){
                    if(uploadedImages[i].id==imageId) {
                        imageUrl=uploadedImages[i].url;
                        break;
                    }
                }
                return (
                    <span>
                        <img
                        src={imageUrl}/>
                    </span>
                )
            },
            customOptionsSelect: function(variationId, field,value) {
                return <span>{value}</span>;
            }
        },
        fieldInputRenderMethods: {
            text: function(variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
                    />
                )
            },
            image: function(variationId, field) {
                var uploadedImages = this.props.uploadedImages.images;
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component={function(props) {
                            return this.renderImageDropdown.call(this,
                                props,
                                variationId,
                                uploadedImages)
                        }.bind(this)}
                    />
                )
            },
            customOptionsSelect: function(variationId, field) {
                return <Field
                    name={field.name}
                    component={this.renderCustomSelect.bind(this, field)}
                />;
            }
        },
        getFieldValueFromState: function(variationId,field){
            var variations = this.props.values.variations;
            var variationSelector = 'variation-'+variationId.toString();
            if( !variations[variationSelector] ){
                return null;
            }
            if(!variations[variationSelector][field.name]){
                return null
            }else{
                console.log('variation field value found for : ', field.name , ' with value : ' , variations[variationSelector][field.name]);
                return variations[variationSelector][field.name];
            }
        },
        renderField: function(variationId, field) {
            var renderFieldMethod = null;

            //todo find value by field id.

            var fieldValue = this.getFieldValueFromState ( variationId,field );


            if(field.isDimensionsField){
                renderFieldMethod = this.fieldInputRenderMethods[field.type].bind(this, variationId, field);
            }else{
                renderFieldMethod = this.fieldNoInputRenderMethods[field.type].bind(this, variationId, field, fieldValue);
            }
            return (
                <td className={'create-variations-table__td'}>
                    {renderFieldMethod()}
                </td>
            )
        },
        renderRow: function(variationId) {
            var fields = this.props.fields;
            return (
                <FormSection name={"variations-" + variationId}>
                    <tr>
                        {fields.map(function(field) {
                            return this.renderField(variationId, field);
                        }.bind(this))}
                    </tr>
                </FormSection>
            )
        },
        renderRows: function() {
            var rows = this.props.rows;
            var rowsToRender = [];
            for (var i = 0; i < rows.length; i++) {
                rowsToRender.push(this.renderRow(rows[i].id));
            }
            return rowsToRender;
        },
        renderTable: function() {
            return (
                <FormSection name={"Variations"}>
                    <table className={'c-table-with-inputs'}>
                        {this.renderHeaderRow()}
                        {this.renderRows()}
                    </table>
                </FormSection>
            );
        },
        render: function() {
            return this.renderTable();
        }
    });

    return DimensionsTableComponent;

});
