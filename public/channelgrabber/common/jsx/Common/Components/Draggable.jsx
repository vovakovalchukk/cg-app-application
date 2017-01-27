define([
    'React',
    'react-dom'
], function(
    React,
    ReactDOM
) {
    "use strict";

    var DraggableComponent = React.createClass({
        getDefaultProps: function () {
            return {
                defaultPosition: {
                    x: 0,
                    y: 0
                }
            };
        },
        getInitialState: function() {
            return {
                dragging: false,
                offsetPosition: null
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
                dragging: true,
                offsetPosition: {
                    x: e.pageX - this.props.defaultPosition.x,
                    y: e.pageY - this.props.defaultPosition.y
                }
            });
            this.props.onMoveStart();
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
            var x = e.pageX - this.state.offsetPosition.x;
            var y = e.pageY - this.state.offsetPosition.y;
            this.props.onMove(x, y);
            e.stopPropagation();
            e.preventDefault();
        },
        render: function() {
            return (
                <div className="draggable-element"
                     onMouseDown={this.onMouseDown}
                     onMouseUp={this.onMouseUp}
                     onMouseMove={this.onMouseMove}
                >
                    {this.props.children}
                </div>
            );
        }
    });

    return DraggableComponent;
});
