import React from 'react';
import styled from 'styled-components';

const Tab = styled.div`
        border:solid;
        background: ${props => props.isCurrentTab ? 'white' : '#ebebeb'};
        color: ${props => props.isCurrentTab ? 'black' : '#3a3a3a'};
        flex: 1 1 60px;
        text-align:center;
        align-items: center;
        display: flex;
        justify-content: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        z-index:10;
        position:relative;
        top: ${props => props.isCurrentTab ? '-4px' : '0px' };
        z-index:10;
        border-color:transparent;
        cursor:pointer;
    `;

class TabComponent extends React.Component {
    static defaultProps = {
        tab: {},
        actions: {},
        isCurrentTab: false
    };

    state = {};

    render() {
        const {isCurrentTab, tab} = this.props;
        return (
            <Tab isCurrentTab={isCurrentTab} onClick={this.props.actions.changeTab.bind(this, tab.key)}>
                {tab.label}
            </Tab>
        );
    }
}

export default TabComponent;