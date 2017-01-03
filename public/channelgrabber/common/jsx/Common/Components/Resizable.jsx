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
                maxSize: {}
            }
        },
        getInititalState: function () {
            return {
                resizing: false,
                size: {}//this.props.defaultSize
            }
        },
        onMouseDown: function (type) {
            this.setState({
                resizing: true,
                direction: type
            });
        },
        onMouseUp: function () {
            this.setState({
                resizing: false
            });
        },
        onMouseMove: function (e) {
            if (! this.state.resizing) {
                return;
            }

            var direction = this.state.direction;
            if (/right/i.test(direction)) {

            }
        },
        render: function () {
            var active =  this.props.active ? ' active' : '';
            var style = {
                width: 100,
                height: 100
            };
            return (
                <div style={style} className={'resizable-component' + active}>
                    {/*<div className={"resizable-anchor top" + active} onMouseDown={this.onMouseDown('top')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor top-right" + active} onMouseDown={this.onMouseDown('top-right')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor top-left" + active} onMouseDown={this.onMouseDown('top-left')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor left" + active} onMouseDown={this.onMouseDown('left')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor right" + active} onMouseDown={this.onMouseDown('right')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor bottom" + active} onMouseDown={this.onMouseDown('bottom')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor bottom-right" + active} onMouseDown={this.onMouseDown('bottom-right')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {/*<div className={"resizable-anchor bottom-left" + active} onMouseDown={this.onMouseDown('bottom-left')} onMouseUp={this.onMouseUp()} onMouseMove={this.onMouseMove}></div>*/}
                    {this.props.children}
                </div>
            );
        }
    });

    return ResizableComponent;
});
