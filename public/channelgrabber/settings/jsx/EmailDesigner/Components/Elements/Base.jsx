define([
    'react',
    'Common/Common/Components/ClickOutside',
    'Common/Common/Components/Resizable'
], function(
    React,
    ClickOutside,
    Resizable
) {
    "use strict";

    var BaseComponent = React.createClass({
        getDefaultProps: function () {
            return {
                id: 0,
                initialPosition: {
                    x: 0,
                    y: 0
                },
                size: {
                    width: 100,
                    height: 100
                }
            }
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
            }, this.fireActivatedEvent());
        },
        onClickOutside: function (e) {
            this.setState({
                dragging:false,
                active:false
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
        onResize: function (x, y) {
            this.setState({
                position: {
                    x: x,
                    y: y
                }
            });
        },
        fireActivatedEvent: function () {

        },
        render: function() {
            var style = {
                cursor: 'pointer',
                position: 'absolute',
                left: this.state.position.x + 'px',
                top: this.state.position.y + 'px'
            };
            return (
                <ClickOutside onClickOutside={this.onClickOutside}>
                <div ref="element"
                     className={this.props.className+" element"}
                     style={style}
                     onClick={this.onClick}
                     onMouseDown={this.onMouseDown}
                     onMouseUp={this.onMouseUp}
                     onMouseMove={this.onMouseMove}
                >
                    <Resizable defaultSize={this.props.size} defaultPosition={this.state.position} active={this.state.active} onResize={this.onResize}>
                        {this.props.children}
                    </Resizable>
                </div>
                </ClickOutside>
            );
        }
    });

    return BaseComponent;
});