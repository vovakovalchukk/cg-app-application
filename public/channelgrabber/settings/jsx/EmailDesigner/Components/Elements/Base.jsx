import PropTypes from 'prop-types';
import React from 'react';
import ClickOutside from 'Common/Common/Components/ClickOutside';
import Resizable from 'Common/Common/Components/Resizable';


class BaseComponent extends React.Component {
    static defaultProps = {
        id: 0,
        initialPosition: {
            x: 0,
            y: 0
        },
        size: {
            width: 100,
            height: 100
        }
    };

    state = {
        position: this.props.initialPosition
    };

    onMouseDown = (e) => {
        this.props.onElementSelected(this.props.id);
        this.setState({
            active: true
        });
    };

    onClickOutside = (e) => {
        this.setState({
            active: false
        });
    };

    onMove = (x, y) => {
        this.setState({
            position: {
                x: x,
                y: y
            }
        });
    };

    onMoveStart = () => {
        this.setState({
            active: true
        });
    };

    render() {
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
}

BaseComponent.propTypes = {
    id: PropTypes.number.isRequired,
    onElementSelected: PropTypes.func.isRequired
};

export default BaseComponent;
