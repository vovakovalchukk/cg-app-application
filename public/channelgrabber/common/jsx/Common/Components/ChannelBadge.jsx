define([
    'react'
], function(
    React
) {
    "use strict";

    var ChannelBadgeComponent = React.createClass({
        getDefaultProps: function() {
            return {
                id: 0,
                channel: '',
                displayName: null,
                onClick: function() {},
                selected: false
            };
        },
        getDisplayName: function() {
            return this.props.displayName ? this.props.displayName : this.props.channel;
        },
        getImageUrl: function() {
            return 'cg-built/setup-wizard/img/channel-badges/' + this.props.channel + '.png';
        },
        onClick: function() {
            this.props.onClick(this.props.id);
        },
        render: function() {
            return (
                <div
                    className="setup-wizard-channel-badge"
                    data-channel={this.props.channel}
                    data-print_name={this.getDisplayName()}
                    onClick={this.onClick}
                >
                    <img
                        src={this.getImageUrl()}
                        alt={this.getDisplayName()}
                        title={this.getDisplayName()}
                    />
                    <div className="setup-wizard-channel-badge-footer">
                        <div className="setup-wizard-channel-badge-name">{this.getDisplayName()}</div>
                    </div>
                </div>
            );
        }
    });

    return ChannelBadgeComponent;
});
