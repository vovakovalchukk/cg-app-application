import React from 'react';
import styled from 'styled-components';
import styleVars from 'Product/Components/ProductList/styleVars';
import Tab from 'Product/Components/ProductList/Components/Tabs/Tab';

let Tabs = styled.div`
        position: relative;
        color: blue;
        width: 400px;
        display: flex;
        flex-wrap: wrap;
        height: ${styleVars.heights.productTabsContainer}px;
        margin-left: auto;
        top: 5px;
    `;
Tabs.wrapper = styled.div`
        position: relative;
        background: ${styleVars.colours.greybg};
        top: ${styleVars.heights.navbar}px;
    `;

class TabsComponent extends React.Component {
    static defaultProps = {
        actions: {},
        tabs: {}
    };

    state = {};

    isCurrentTab = (tab) => {
        return this.props.tabs.currentTab === tab.key;
    };

    renderTabs = () => {
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
    };

    render() {
        return (
            <Tabs.wrapper>
                <Tabs>
                    {this.renderTabs()}
                </Tabs>
            </Tabs.wrapper>
        );
    }
}

export default TabsComponent;