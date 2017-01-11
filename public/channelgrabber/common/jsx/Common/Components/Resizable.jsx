define([
    'react',
    'Common/Common/Components/Draggable'
], function(
    React,
    Draggable
) {
    "use strict";

    var ResizableComponent = React.createClass({
        getDefaultProps: function () {
            return {
                active: false,
                snapSize: 1,
                lockAspectRatio: false,
                defaultSize: {
                    width: 100,
                    height: 100
                },
                defaultPosition: {
                    x: 0,
                    y: 0
                },
                maxSize: {},
                directions: [
                    'top', 'top-right', 'top-left',
                    'left', 'right',
                    'bottom', 'bottom-left', 'bottom-right'
                ]
            }
        },
        getInitialState: function () {
            return {
                active: this.props.active,
                resizing: false,
                direction: '',
                size: this.props.defaultSize,
                originalMousePos: {
                    x: 0,
                    y: 0
                }
            }
        },
        componentWillReceiveProps: function (newProps) {
            this.setState({
                active: newProps.active
            });
        },
        componentDidMount: function () {
            window.addEventListener('mouseup', this.onMouseUp.bind(this));
            window.addEventListener('mousemove', this.onMouseMove.bind(this));
        },
        componentWillUnmount: function () {
            window.removeEventListener('mouseup', this.onMouseUp.bind(this));
            window.removeEventListener('mousemove', this.onMouseMove.bind(this));
        },
        onMouseDown: function (e, type) {
            this.setState({
                resizing: true,
                direction: type,
                originalMousePos: {
                    x: e.clientX,
                    y: e.clientY
                }
            });
            e.stopPropagation();
            e.preventDefault();
        },
        onMouseUp: function (e) {
            this.setState({
                resizing: false,
                direction: ''
            });
            e.stopPropagation();
            e.preventDefault();
        },
        cancelResize: function (e) {
            this.setState({
                resizing: false,
                direction: ''
            });
            e.stopPropagation();
            e.preventDefault();
        },
        onMouseMove: function (e) {
            if (! this.state.resizing || ! this.state.active) {
                return;
            }

            var differenceX = e.clientX - this.state.originalMousePos.x;
            var differenceY = e.clientY - this.state.originalMousePos.y;
            var direction = this.state.direction;
            var newWidth = this.state.size.width;
            var newHeight = this.state.size.height;
            var newX = this.props.defaultPosition.x;
            var newY = this.props.defaultPosition.y;

            if (/right/i.test(direction)) {
                newWidth += differenceX;
            }
            if (/left/i.test(direction)) {
                newWidth -= differenceX;
                newX += differenceX;
            }
            if (/top/i.test(direction)) {
                newHeight -= differenceY;
                newY += differenceY;
            }
            if (/bottom/i.test(direction)) {
                newHeight += differenceY;
            }

            this.setState({
                size: {
                    width: newWidth,
                    height: newHeight
                },
                originalMousePos: {
                    x: e.clientX,
                    y: e.clientY
                }
            });
            this.props.onMove(newX, newY);
            e.stopPropagation();
            e.preventDefault();
        },
        render: function () {
            var active =  this.state.active ? ' active' : '';
            var style = {
                width: this.state.size.width,
                height: this.state.size.height
            };
            return (
                <Draggable defaultPosition={this.props.defaultPosition} onMove={this.props.onMove} onMoveStart={this.props.onMoveStart}>
                    <div ref={function (element) {this.element = element;}.bind(this)}
                         style={style}
                         className={'resizable-element' + active}
                         onMouseUp={function(e){this.cancelResize(e)}.bind(this)}>
                        {this.props.directions.map(function(direction) {
                            return (
                                <div className={"resizable-anchor " + direction + " " + active}
                                     onMouseDown={function(e){this.onMouseDown(e, direction)}.bind(this)}></div>
                            )
                        }.bind(this))}
                        {this.props.children}
                    </div>
                </Draggable>
            );
        }
    });

    return ResizableComponent;
});
