define([
    'react',
    'styled-components'
], function(
    React,
    styled
) {
    "use strict";
    
    styled = styled.default;
    
    const styleVariables = {
        colours:{
            bluegrey: '#d2dce1',
            greybg:'#f5f5f5;'
        },
        navbar: {
            height: 50
        },
        productTabsContainer: {
            height: 30
        }
    };
    
    const Tabs = styled.div`
        position:relative;
        color:blue;
        width:500px;
        display: flex;
        flex-wrap: wrap;
        height:${styleVariables.productTabsContainer.height}px;
        margin-left:auto;
    `;
    
    Tabs.wrapper = styled.div`
        background:yellow;
        position:relative;
        background: ${styleVariables.colours.greybg};
        top: ${styleVariables.navbar.height}px;
    `;
    //
    Tabs.tab = styled.div`
        border:solid;
        background: ${props => props.current ? 'white' : 'grey'};
        color: ${props => props.current ? 'black' : 'white'};
        flex: 1 1 60px;
        text-align:center;
        align-items: center;
        display: flex;
        justify-content: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        z-index:10;
        position:relative;
        height: ${props => props.current ? '30px' : 'auto' };
        top: ${props => props.current ? '-4px' : '0px' };
        z-index:10;
        border-color:transparent;
    `;
    
    var TabsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                actions:{}
            };
        },
        getInitialState: function() {
            return {}
        },
        render: function() {
            console.log('in render of Tabs component with actions: ' , this.props.actions);
            
            
            return (
                <Tabs.wrapper>
                    <Tabs>
                        <Tabs.tab current>
                            Listings
                        </Tabs.tab>
                        <Tabs.tab>
                            Details
                        </Tabs.tab>
                        <Tabs.tab>
                            Vat
                        </Tabs.tab>
                    </Tabs>
                </Tabs.wrapper>
            );
        }
    });
    
    
    return TabsComponent;
})
;
