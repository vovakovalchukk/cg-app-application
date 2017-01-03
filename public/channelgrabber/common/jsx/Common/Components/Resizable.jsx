define([
    'react',
    'react-dom'
], function(
    React,
    ReactDOM
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
                resizing: false,
                direction: '',
                size: this.props.defaultSize,
                originalMousePos: {
                    x: 0,
                    y: 0
                }
            }
        },
        onMouseDown: function (e, type) {
            console.log(type);
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
        onMouseMove: function (e) {
            if (! this.state.resizing) {
                return;
            }

            var differenceX = e.clientX - this.state.originalMousePos.x;
            var differenceY = e.clientY - this.state.originalMousePos.y;
            console.log(differenceX);
            console.log(differenceY);
            var direction = this.state.direction;
            var newWidth = this.state.size.width;
            var newHeight = this.state.size.height;
            if (/right/i.test(direction)) {
                console.log('right movement');
                newWidth += differenceX;
            }
            if (/left/i.test(direction)) {
                console.log('left movement');
                newWidth -= differenceX;
            }
            if (/top/i.test(direction)) {
                console.log('top movement');
                newHeight -= differenceY;
            }
            if (/bottom/i.test(direction)) {
                console.log('bottom movement');
                newHeight += differenceY;
            }
            this.element.width = newWidth;
            this.element.height = newHeight;
            e.stopPropagation();
            e.preventDefault();
        },
        render: function () {
            var active =  this.props.active ? ' active' : '';
            var style = {
                width: this.state.size.width,
                height: this.state.size.height
            };
            return (
                <div ref={function (element) {this.element = element;}.bind(this)}
                     style={style}
                     className={'resizable-component' + active}>
                    {this.props.directions.map(function(direction) {
                        return (
                            <div className={"resizable-anchor " + direction + " " + active}
                                 onMouseDown={function(e){this.onMouseDown(e, direction)}.bind(this)}
                                 onMouseUp={function(e){this.onMouseUp(e)}.bind(this)}
                                 onMouseMove={function(e){this.onMouseMove(e)}.bind(this)}></div>
                        )
                    }.bind(this))}
                    {this.props.children}
                </div>
            );
        }
    });

    return ResizableComponent;
});
