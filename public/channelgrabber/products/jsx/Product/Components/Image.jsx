define([
    'react',
    'react-tether'
], function(
    React,
    TetherComponent
) {
    "use strict";

    var ImageComponent = React.createClass({
        getInitialState: function() {
            return {
                hover: false
            }
        },
        onMouseOver: function () {
            this.setState({ hover: true });
        },
        onMouseOut: function () {
            this.setState({ hover: false });
        },
        render: function() {
            var hoverImageStyle = {
                display: (this.state.hover ? "block" : "none")
            };
            return (
                <TetherComponent
                    attachment="middle left"
                    targetAttachment="middle right"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    <img
                        src={this.props.src}
                        onMouseOver={this.onMouseOver}
                        onMouseOut={this.onMouseOut}
                    />
                    <div className="hover-image" style={hoverImageStyle}>
                        <img src={this.props.src} />
                    </div>
                </TetherComponent>
            );
        }
    });

    return ImageComponent;
});