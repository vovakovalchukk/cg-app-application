import React from 'react';
import TetherComponent from 'react-tether';
import ClickOutside from 'Common/Components/ClickOutside';
import ImagePicker from 'Common/Components/ImagePicker';
    

    var ImageDropDownComponent = React.createClass({
        getDefaultProps: function() {
            return {
                selected: null,
                autoSelectFirst: true,
                images: [],
                onChange: null,
                dropdownDisabled: false
            };
        },
        getInitialState: function() {
            return {
                active: false,
                image: null
            }
        },
        componentDidMount() {
            this.onImageSelected(this.props.selected);
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
            if (!this.props.dropdownDisabled) {
                return;
            }
            this.setState({
                active: !this.state.active
            });
        },
        onImageSelected: function(image) {
            this.setState({
                active: false,
                image: image
            });
            this.props.onChange({target: {value: image.id}});
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
                            {this.state.image
                                ? <span className="react-image-picker-image"><img src={this.state.image.url}/></span>
                                : <span className="react-image-picker-select-text">Select an image</span>
                            }
                            {!this.props.dropdownDisabled
                                ? <span className={"sprite-arrow-" + (this.state.active ? "up" : "down") + "-10-black"}>&nbsp;</span>
                                : ''
                            }
                        </div>
                    </ClickOutside>
                    <div style={{display: this.state.active ? 'initial' : 'none'}}>
                        <ImagePicker
                            autoSelectFirst={this.props.autoSelectFirst}
                            multiSelect={false}
                            images={this.props.images}
                            onImageSelected={this.onImageSelected}
                        />
                    </div>
                </TetherComponent>
            );
        }
    });

    export default ImageDropDownComponent;

