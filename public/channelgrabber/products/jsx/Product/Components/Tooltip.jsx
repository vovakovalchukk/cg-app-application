import React from 'react';
import TetherComponents from 'react-tether';

"use strict";

let TooltipComponent = React.createClass({
    getDefaultProps: function() {
        return {
            hoverContent: null
        }
    },
    getInitialState: function() {
        return {
            isOpen: false
        }
    },
    render: function() {
        return (
            <TetherComponent
                attachment="top left"
                targetAttachment="middle right"
                constraints={[{
                    to: 'scrollParent',
                    attachment: 'together'
                }]}
                className='tooltip-hover'
            >
                <div className={'trigger'} onMouseOver={() => {
                    this.setState({isOpen: true})
                }} onMouseOut={() => {
                    this.setState({isOpen: false})
                }}>
                    {this.props.children}
                </div>
                {this.state.isOpen && <div className='tooltip-hover'>
                    {this.props.hoverContent}
                </div>}
            </TetherComponent>
        );
    }
});

export default TooltipComponent;