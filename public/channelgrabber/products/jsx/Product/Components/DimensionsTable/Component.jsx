define([
    'react',
    'redux-form',
    'Common/Components/ImageDropDown',
    'Common/Components/Select',
    'Product/Components/CreateProduct/StockModeInputs/Root'

], function(
    React,
    reduxForm,
    ImageDropDown,
    Select,
    StockModeInputs
) {

    var Field = reduxForm.Field;
    var Fields = reduxForm.Fields;
    var FormSection = reduxForm.FormSection;

    var DimensionsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                fields:[],
                rows:[{}],
                values:[],
                formSectionName:null
            };
        },
        renderHeadings:function(){

        },
        renderHeadings: function() {
            console.log("dimensionsComponent this.props : ", this.props);
            return this.props.fields.map(function(field) {
                return this.renderHeading(field);
            }.bind(this));
        },
        renderHeading: function(field) {
            var jsx =  (
                    <th className={'' +
                    'c-table-with-inputs__cell ' +
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
        renderTable: function(variations) {
            return (
                <FormSection name={"variVariationations"}>
                    <table className={'c-table-with-inputs'}>
                        {this.renderHeaderRow()}
                        {/*{this.renderRows()}*/}
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
