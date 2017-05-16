define([
    'react',
    'react-tether'
], function(
    React,
    TetherComponent
) {
    "use strict";

    var LinkComponent = React.createClass({
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
            var spriteClass = (this.props.linkedProducts.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
            return (
                <TetherComponent
                    attachment="middle left"
                    targetAttachment="middle right"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    <span className={"sprite "+ spriteClass}
                            onMouseOver={this.onMouseOver}
                            onMouseOut={this.onMouseOut}
                    ></span>
                    <div className="hover-link"
                         style={hoverImageStyle}
                         onMouseOver={this.onMouseOver}
                         onMouseOut={this.onMouseOut}>
                        {this.props.linkedProducts.map(function (linkedProduct) {
                            return (
                                <div className="hover-link-row">
                                    <span></span>
                                </div>
                            );
                        })}
                    </div>
                </TetherComponent>
            );
        }
    });

    return LinkComponent;
});