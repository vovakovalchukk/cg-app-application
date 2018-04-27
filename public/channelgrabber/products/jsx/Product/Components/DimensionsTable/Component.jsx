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
//                newVariationRowRequest: null
            };
        },
        render: function() {
           console.log("in render method of Dimensions Table");
           return (
               <div>IN DIMENSIONS TABLE</div>
           )
        }
    });

    return DimensionsTableComponent;


});
