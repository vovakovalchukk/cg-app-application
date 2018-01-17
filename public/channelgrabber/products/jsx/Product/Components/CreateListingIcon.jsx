define([
    'react',
    'react-tether'
], function(
    React,
    TetherComponent
) {
    "use strict";

    var CreateListingIconComponent = React.createClass({
        getInitialState: function() {
            return {
                hover: false
            }
        },
        onMouseOver: function () {
            this.setState({ hover: true });
        },
        onMouseOut: function () {
            this.setState({ hover: false });
        },
        render: function() {
            if (!this.props.isVariation) {
                return <i className="fa fa-plus icon-create-listing" aria-hidden="true" />
            }

            var hoverImageStyle = {
                display: (this.state.hover ? "block" : "none")
            };

            return  <TetherComponent
                attachment="top left"
                targetAttachment="middle right"
                constraints={[{
                    to: 'scrollParent',
                    attachment: 'together'
                }]}
            >
                <i
                    className="fa fa-plus icon-create-listing inactive"
                    onMouseOver={this.onMouseOver.bind(this)}
                    onMouseOut={this.onMouseOut.bind(this)}
                    aria-hidden="true"
                />
                <div
                    className="hover-link"
                     style={hoverImageStyle}
                >
                    <p>We only currently support creating listings on eBay accounts for simple products.</p>
                    <p>We're working hard to add support for other channels so check back soon.</p>
                </div>
            </TetherComponent>;
        }
    });

    return CreateListingIconComponent;
});