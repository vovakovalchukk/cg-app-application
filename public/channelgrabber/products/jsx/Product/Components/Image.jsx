import React from 'react';
import TetherComponent from 'react-tether';


class ImageComponent extends React.Component {
    state = {
        hover: false
    };

    onMouseOver = () => {
        this.setState({ hover: true });
    };

    onMouseOut = () => {
        this.setState({ hover: false });
    };

    render() {
        var hoverImageStyle = {
            display: (this.state.hover ? "block" : "none")
        };
        return (
            <TetherComponent
                attachment="middle left"
                targetAttachment="middle right"
                constraints={[{
                    to: 'scrollParent',
                    attachment: 'together'
                }]}
            >
                <img
                    src={this.props.src}
                    onMouseOver={this.onMouseOver}
                    onMouseOut={this.onMouseOut}
                />
                <div className="hover-image" style={hoverImageStyle}>
                    <img src={this.props.src} />
                </div>
            </TetherComponent>
        );
    }
}

export default ImageComponent;
