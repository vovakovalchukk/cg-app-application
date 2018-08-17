define([
    'react',
    'styled-components',
    'Product/Components/ProductList/styleVars',
    'Product/Components/ProductList/Components/Tabs/Tab'
], function(
    React,
    styled,
    styleVars,
    Tab
) {
    "use strict";
    
    styled = styled.default;
    
    const Tabs = styled.div`
        position:relative;
        color:blue;
        width:500px;
        display: flex;
        flex-wrap: wrap;
        height:${styleVars.heights.productTabsContainer}px;
        margin-left:auto;
        top:5px;
    `;
    
    Tabs.wrapper = styled.div`
        position:relative;
        background: ${styleVars.colours.greybg};
        top: ${styleVars.heights.navbar}px;
    `;
    
    var TabsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                actions: {},
                tabs: {}
            };
        },
        getInitialState: function() {
            return {}
        },
        isCurrentTab: function(tab) {
            return this.props.tabs.currentTab === tab.key;
        },
        renderTabs: function() {
            const {tabs} = this.props.tabs;
            return tabs.map((tab) => {
                return (
                    <Tab
                        isCurrentTab={this.isCurrentTab(tab)}
                        actions={this.props.actions}
                        tab={tab}
                    />
                )
            })
        },
        render: function() {
            return (
                <Tabs.wrapper>
                    <Tabs>
                        {this.renderTabs()}
                    </Tabs>
                </Tabs.wrapper>
            );
        }
    });
    
    return TabsComponent;
});
