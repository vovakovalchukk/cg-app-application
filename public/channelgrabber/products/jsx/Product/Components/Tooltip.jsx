import React from 'react';
import TetherComponent from 'react-tether';

"use strict";

class TooltipComponent extends React.Component {
    static defaultProps = {
        hoverContent: null
    };

    state = {
        isOpen: false
    };

    render() {
        return (
            <TetherComponent
                attachment="top left"
                targetAttachment="middle right"
                constraints={[{
                    to: 'scrollParent',
                    attachment: 'together'
                }]}
                className='tooltip-hover u-ease_0-1'
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
}

export default TooltipComponent;