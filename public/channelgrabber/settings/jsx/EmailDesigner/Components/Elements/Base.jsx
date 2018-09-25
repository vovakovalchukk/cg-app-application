import PropTypes from 'prop-types';
import React from 'react';
import ClickOutside from 'Common/Common/Components/ClickOutside';
import Resizable from 'Common/Common/Components/Resizable';


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
            position: this.props.initialPosition
        };
    },
    onMouseDown: function (e) {
        this.props.onElementSelected(this.props.id);
        this.setState({
            active: true
        });
    },
    onClickOutside: function (e) {
        this.setState({
            active: false
        });
    },
    onMove: function (x, y) {
        this.setState({
            position: {
                x: x,
                y: y
            }
        });
    },
    onMoveStart: function () {
        this.setState({
            active: true
        });
    },
    render: function() {
        var position = {
            left: this.state.position.x + 'px',
            top: this.state.position.y + 'px'
        };
        return (
            <ClickOutside onClickOutside={this.onClickOutside}>
            <div className={this.props.className+" element"}
                 style={position}
                 onMouseDown={this.onMouseDown}
            >
                <Resizable defaultSize={this.props.size}
                           defaultPosition={this.state.position}
                           active={this.state.active}
                           onMove={this.onMove}
                           onMoveStart={this.onMoveStart}
                >
                    {this.props.children}
                </Resizable>
            </div>
            </ClickOutside>
        );
    }
});

BaseComponent.propTypes = {
    id: PropTypes.number.isRequired,
    onElementSelected: PropTypes.func.isRequired
};

export default BaseComponent;
