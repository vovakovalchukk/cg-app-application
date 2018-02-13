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
            var index = $(event.target).data().index;
            this.props.onRemoveButtonClick(index);
        },
        renderPlusButton: function (index) {
            return <span className="refresh-icon">
                <i
                    className='fa fa-2x fa-plus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onPlusButtonClick}
                    data-index={index}
                />
            </span>;
        },
        render: function () {
            return <label>
                <span className={"inputbox-label container-extra-item-specific"}>
                    <Input
                        name={this.getCustomInputName(this.props.index)}
                    />
                </span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={this.getCustomInputValueName(this.props.index)}
                    />
                </div>
                {this.renderPlusButton(this.props.index)}
            </label>;
        }
    });
});
