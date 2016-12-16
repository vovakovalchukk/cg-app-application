define([
    'react'
], function(
    React
) {
    "use strict";

    var TextComponent = React.createClass({
        getDefaultProps: function() {
            return {
                text: "",
                initialPosition: {
                    x: 0,
                    y: 0
                }
            };
        },
        getInitialState: function() {
            return {
                position: this.props.initialPosition,
                dragging: false
            };
        },
        componentDidUpdate: function (props, state) {
            if (this.state.dragging && !state.dragging) {
                document.addEventListener('mousemove', this.onMouseMove);
                document.addEventListener('mouseup', this.onMouseUp);
            } else if (!this.state.dragging && state.dragging) {
                document.removeEventListener('mousemove', this.onMouseMove);
                document.removeEventListener('mouseup', this.onMouseUp);
            }
        },
        onMouseDown: function (e) {
            if (e.button !== 0) {
                return;
            }
            this.setState({
                dragging: true
            });
            e.stopPropagation();
            e.preventDefault();
        },
        onMouseUp: function (e) {
            this.setState({
                dragging: false
            });
            e.stopPropagation();
            e.preventDefault();
        },
        onMouseMove: function (e) {
            if (!this.state.dragging) {
                return;
            }
            this.setState({
                position: {
                    x: e.pageX,
                    y: e.pageY
                }
            });
            e.stopPropagation();
            e.preventDefault();
        },
        render: function() {
            var style = {
                cursor: 'pointer',
                position: 'absolute',
                left: this.state.position.x + 'px',
                top: this.state.position.y + 'px'
            };
            return (
                <div style={style} className="element-view text-element">
                    {this.props.text + " hey"}
                </div>
            );
        }
    });

    return TextComponent;
});