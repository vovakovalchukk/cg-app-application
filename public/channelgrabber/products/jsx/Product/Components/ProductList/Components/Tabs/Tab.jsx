define([
    'react',
    'styled-components',
    'Product/Components/ProductList/styleVars'
], function(
    React,
    styled,
    styleVars
) {
    "use strict";
    
    styled = styled.default;
    
    const Tab = styled.div`
        border:solid;
        background: ${props => props.isCurrentTab ? 'white' : 'grey'};
        color: ${props => props.isCurrentTab ? 'black' : 'white'};
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
    
    var TabComponent = React.createClass({
        getDefaultProps: function() {
            return {
                tab:{},
                actions:{},
                isCurrentTab:false
            };
        },
        getInitialState: function() {
            return {}
        },
        render: function() {
            const {isCurrentTab, tab} = this.props;
            return (
                <Tab isCurrentTab={isCurrentTab} onClick={this.props.actions.changeTab.bind(this, tab.key)}>
                    {tab.label}
                </Tab>
            );
        }
    });
    
    return TabComponent;
});
