define([
    'react',
    'redux-form',
    'Common/Components/Input'
], function(
    React,
    ReduxForm,
    Input
) {
    var Field = ReduxForm.Field;

    return React.createClass({
        getDefaultProps: function() {
            return {
                index: null,
                name: null,
                value: null,
                onRemoveButtonClick: null,
                onChange: null,
                input: null
            };
        },
        getDefaultState: function() {
            return {
                nameInput: null,
                valueInput: null
            };
        },
        shouldComponentUpdate: function(nextProps, nextState) {
            // We're going to ignore state changes as we're just using that to keep a handle on the inputs
            // and we don't want that to trigger a re-render
            return this.havePropsChanged(nextProps);
        },
        havePropsChanged: function(nextProps) {
            for (var key in this.props) {
                if (typeof nextProps[key] == 'undefined' || nextProps[key] != this.props[key]) {
                    return true;
                }
            }
            return false;
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
            this.state.nameInput.value = '';
            this.state.nameInput.onChange('');
            this.state.valueInput.value = '';
            this.state.valueInput.onChange('');
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
            this.setState({nameInput: field.input});
            return (<span className={"inputbox-label container-extra-item-specific"}>
                <Input
                    name={field.input.name}
                    value={this.props.name}
                    onChange={this.onNameChange.bind(this, this.props.index, field.input)}
                />
            </span>);
        },
        renderValue: function(field) {
            this.setState({valueInput: field.input});
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
});
