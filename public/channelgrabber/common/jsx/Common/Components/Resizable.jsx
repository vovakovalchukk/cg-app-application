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
                startWidth: 100,
                startHeight: 100,
                maxWidth: 0,
                maxHeight: 0,
                snapSize: 1,
                lockAspectRatio: false
            }
        },
        getInititalState: function () {
            return {
                resizing: false
            }
        },
        onMouseDown: function (type) {
            console.log(type);
        },
        render: function () {
            var active =  this.props.active ? ' active' : '';
            return (
                <div className={'resizable-component' + active}>
                    <div className={"resizable-anchor top" + active}></div>
                    <div className={"resizable-anchor top-right" + active}></div>
                    <div className={"resizable-anchor top-left" + active}></div>
                    <div className={"resizable-anchor left" + active}></div>
                    <div className={"resizable-anchor right" + active}></div>
                    <div className={"resizable-anchor bottom" + active}></div>
                    <div className={"resizable-anchor bottom-right" + active}></div>
                    <div className={"resizable-anchor bottom-left" + active}></div>
                    {this.props.children}
                </div>
            );
        }
    });

    return ResizableComponent;
});
