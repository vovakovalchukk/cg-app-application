define([
    'react',
    'react-tether',
    'Common/Components/ClickOutside',
    'Common/Components/ImagePicker'
], function(
    React,
    TetherComponent,
    ClickOutside,
    ImagePicker
) {
    "use strict";

    var ImageDropDownComponent = React.createClass({
        getDefaultProps: function() {
            return {
                selected: null,
                images: [],
                onChange: null
            };
        },
        getInitialState: function() {
            return {
                active: false,
                image: this.props.selected || {url: "/cg-built/products/img/noproductsimage.png"}
            }
        },
        onClickOutside: function(event) {
            if (event.target.closest(".image-dropdown-element")) {
                return;
            }
            this.setState({
                active: false
            });
        },
        onClick: function() {
            this.setState({
                active: !this.state.active
            });
        },
        onImageSelected: function(image) {
            this.setState({
                active: false,
                image: image
            });
        },
        componentDidUpdate: function(prevProps, prevState) {
            if (this.props.onChange && prevState.image.id !== this.state.image.id) {
                var input = document.createElement('input');
                input.value = this.state.image.id;
                this.props.onChange({
                    target: input
                });
            }
        },
        render: function() {
            return (
                <TetherComponent
                    attachment="top left"
                    targetAttachment="bottom left"
                    classPrefix="image-dropdown"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    <ClickOutside onClickOutside={this.onClickOutside}>
                        <div className="react-image-picker" onClick={this.onClick}>
                            <span className="react-image-picker-image"><img src={this.state.image.url}/></span>
                            <span className={"sprite-arrow-" + (this.state.active ? "up" : "down") + "-10-black"}>&nbsp;</span>
                        </div>
                    </ClickOutside>
                    <div style={{display: this.state.active ? 'initial' : 'none'}}>
                        <ImagePicker
                            multiSelect={false}
                            images={this.props.images}
                            onImageSelected={this.onImageSelected}
                        />
                    </div>
                </TetherComponent>
            );
        }
    });

    return ImageDropDownComponent;
});