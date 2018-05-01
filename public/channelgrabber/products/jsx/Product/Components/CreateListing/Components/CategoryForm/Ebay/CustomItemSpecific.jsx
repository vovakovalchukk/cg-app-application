define([
    'react',
    'react-redux',
    'redux-form',
    'Common/Components/Input'
], function(
    React,
    ReactRedux,
    ReduxForm,
    Input
) {
    var Field = ReduxForm.Field;

    const CustomItemSpecific = React.createClass({
        getDefaultProps: function() {
            return {
                index: null,
                name: null,
                value: null,
                categoryId: null,
                onRemoveButtonClick: null,
                onChange: null,
                resetField: null
            };
        },
        getCustomInputName: function(index) {
            return 'CustomInputName' + index;
        },
        getCustomInputValueName: function(index) {
            return 'CustomInputValueName' + index;
        },
        onRemoveButtonClick: function(index, event) {
            this.resetFields();
            this.props.onRemoveButtonClick(index);
        },
        resetFields: function() {
            var nameFieldName = 'category.id-' + this.props.categoryId + '.' + this.getCustomInputName(this.props.index);
            var valueFieldName = 'category.id-' + this.props.categoryId + '.' + this.getCustomInputValueName(this.props.index);
            this.props.resetField(nameFieldName);
            this.props.resetField(valueFieldName);
        },
        onNameChange: function(index, input, event) {
            var value = event.target.value;
            this.onInputChange(index, 'name', value, input);
        },
        onValueChange: function(index, input, event) {
            var value = event.target.value;
            this.onInputChange(index, 'value', value, input);
        },
        onInputChange: function(index, type, value, input) {
            input.onChange(value);
            this.props.onChange(index, type, value);
        },
        renderRemoveButton: function (index) {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x fa-minus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onRemoveButtonClick.bind(this, index)}
                />
            </span>;
        },
        renderName: function(field) {
            return (<span className={"inputbox-label container-extra-item-specific"}>
                <Input
                    name={field.input.name}
                    value={this.props.name}
                    onChange={this.onNameChange.bind(this, this.props.index, field.input)}
                />
            </span>);
        },
        renderValue: function(field) {
            return (<div className={"order-inputbox-holder"}>
                <Input
                    name={field.input.name}
                    value={this.props.value}
                    onChange={this.onValueChange.bind(this, this.props.index, field.input)}
                />
            </div>);
        },
        render: function () {
            var nameFieldName = this.getCustomInputName(this.props.index);
            var valueFieldName = this.getCustomInputValueName(this.props.index);
            // Do NOT use bind() on the component functions, it will cause the fields to keep losing focus
            // https://redux-form.com/7.3.0/docs/api/field.md/#2-a-stateless-function
            return <label>
                <Field name={nameFieldName} component={this.renderName} />
                <Field name={valueFieldName} component={this.renderValue} />
                {this.renderRemoveButton(this.props.index)}
            </label>;
        }
    });

    const mapStateToProps = null;
    const mapDispatchToProps = function(dispatch) {
        return {
            resetField: function(fieldName) {
                dispatch(ReduxForm.change('createListing', fieldName, ''));
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CustomItemSpecific);
});
