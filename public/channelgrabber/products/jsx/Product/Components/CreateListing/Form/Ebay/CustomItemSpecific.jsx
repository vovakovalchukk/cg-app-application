define([
    'react',
    'Common/Components/Input'
], function(
    React,
    Input
) {
    return React.createClass({
        getCustomInputName: function(index) {
            return 'CustomInputName' + index;
        },
        getCustomInputValueName: function(index) {
            return 'CustomInputValueName' + index;
        },
        onRemoveButtonClick: function(index) {
            this.props.onRemoveButtonClick(index);
        },
        onNameChange: function(index, event) {
            var value = event.target.value;
            this.onInputChange(index, 'name', value);
        },
        onValueChange: function(index, event) {
            var value = event.target.value;
            this.onInputChange(index, 'value', value);
        },
        onInputChange: function(index, type, value) {
            this.props.onChange(index, type, value);
        },
        renderRemoveButton: function (index) {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x  fa-trash-o'
                    aria-hidden='true'
                    onClick={this.onRemoveButtonClick.bind(this, index)}
                />
            </span>;
        },
        render: function () {
            return <label>
                <span className={"inputbox-label container-extra-item-specific"}>
                    <Input
                        name={this.getCustomInputName(this.props.index)}
                        value={this.props.name}
                        onChange={this.onNameChange.bind(this, this.props.index)}
                    />
                </span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={this.getCustomInputValueName(this.props.index)}
                        value={this.props.value}
                        onChange={this.onValueChange.bind(this, this.props.index)}
                    />
                </div>
                {this.renderRemoveButton(this.props.index)}
            </label>;
        }
    });
});
