define([
    'react',
    'react-tether'
], function(
    React,
    TetherComponent,
) {
    "use strict";

    var TooltipComponent = React.createClass({
        getDefaultProps: function () {
            return {
                hoverContent: null,
                children: ['one', 'two']
            }
        },
        getInitialState: function() {
            return {
                isOpen: false
            }
        },
        render: function() {
            return  (
                <TetherComponent
                    attachment="top left"
                    targetAttachment="middle right"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    <div className={'trigger'} onMouseOver={() => {this.setState({isOpen: true})}} onMouseOut={() => {this.setState({isOpen: false})}}>
                        {this.props.children}
                    </div>
                    {this.state.isOpen &&  <div>
                        {this.props.hoverContent}
                    </div>}
                </TetherComponent>
            );
        }
    });

    return TooltipComponent;
});