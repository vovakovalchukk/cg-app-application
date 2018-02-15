define([
    'react',
    'Common/Components/Input'
], function(
    React,
    Input
) {
    return React.createClass({
        getInitialState: function() {
            return {}
        },
        getCustomInputName: function(index) {
            return 'CustomInputName' + index;
        },
        getCustomInputValueName: function(index) {
            return 'CustomInputValueName' + index;
        },
        onRemoveButtonClick: function(event) {
            var index = event.target.dataset.index;
            this.props.onRemoveButtonClick(index);
        },
        onNameChange: function(event) {
            this.onInputChange('name', event);
        },
        onValueChange: function(event) {
            this.onInputChange('value', event);
        },
        onInputChange: function(type, event) {
            var index = event.target.parentElement.parentElement.dataset.index;
            this.props.onChange(index, type, event.target.value);
        },
        renderRemoveButton: function (index) {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x fa-minus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onRemoveButtonClick}
                    data-index={index}
                />
            </span>;
        },
        render: function () {
            return <label>
                <span className={"inputbox-label container-extra-item-specific"} data-index={this.props.index}>
                    <Input
                        name={this.getCustomInputName(this.props.index)}
                        value={this.props.name}
                        onChange={this.onNameChange}
                    />
                </span>
                <div className={"order-inputbox-holder"} data-index={this.props.index}>
                    <Input
                        name={this.getCustomInputValueName(this.props.index)}
                        value={this.props.value}
                        onChange={this.onValueChange}
                    />
                </div>
                {this.renderRemoveButton(this.props.index)}
            </label>;
        }
    });
});
