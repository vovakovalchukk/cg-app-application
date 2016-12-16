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
        onClick: function (e) {
            this.setState({
                active: true
            });
        },
        onMouseDown: function (e) {
            if (e.button !== 0) {
                return;
            }
            this.setState({
                dragging: true,
                active: true,
                offsetPosition: {
                    x: e.pageX - this.refs.element.offsetLeft,
                    y: e.pageY - this.refs.element.offsetTop
                }
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
                    x: e.pageX - this.state.offsetPosition.x,
                    y: e.pageY - this.state.offsetPosition.y
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
                <div
                    ref="element"
                    className={"element text-element" + (this.state.active ? ' active' : '')}
                    style={style}
                    onClick={this.onClick}
                    onMouseDown={this.onMouseDown}
                    onMouseUp={this.onMouseUp}
                    onMouseMove={this.onMouseMove}
                >
                    {this.props.text}
                </div>
            );
        }
    });

    return TextComponent;
});