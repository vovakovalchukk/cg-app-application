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
        onPlusButtonClick: function(event) {
            var index = event.target.dataset.index;
            this.props.onRemoveButtonClick(index);
        },
        onChange: function(event) {
            var index = $(event.target).parent().parent().data().index;
            this.props.onChange(index);
        },
        renderRemoveButton: function (index) {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x fa-minus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onPlusButtonClick}
                    data-index={index}
                />
            </span>;
        },
        render: function () {
            return <label>
                <span className={"inputbox-label container-extra-item-specific"} data-index={this.props.index}>
                    <Input
                        name={this.getCustomInputName(this.props.index)}
                        onChange={this.onChange}
                    />
                </span>
                <div className={"order-inputbox-holder"} data-index={this.props.index}>
                    <Input
                        name={this.getCustomInputValueName(this.props.index)}
                        onChange={this.onChange}
                    />
                </div>
                {this.renderRemoveButton(this.props.index)}
            </label>;
        }
    });
});
