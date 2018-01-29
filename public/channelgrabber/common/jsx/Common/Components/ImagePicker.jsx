define([
    'react'
], function(
    React
) {
    "use strict";

    var ImagePickerComponent = React.createClass({
        getDefaultProps: function() {
            return {
                name: '',
                className: '',
                multiSelect: true,
                images: [],
                onImageSelected: null,
                autoSelectFirst: true,
                title: null
            };
        },
        getInitialState: function() {
            return {
                selectedImages: []
            };
        },
        componentDidMount: function() {
            var selectedImages = [];
            this.props.images.forEach(function(image) {
                if (image.selected) {
                    selectedImages.push(image);
                }
            })
            if (this.props.autoSelectFirst && selectedImages.length == 0 && this.props.images.length > 0) {
                selectedImages.push(this.props.images[0]);
            }

            selectedImages.forEach(function(image) {
                this.imageSelected(image);
            }.bind(this))
        },
        imageSelected: function(image) {
            var currentlySelectedImages = this.state.selectedImages.slice(0);
            var selectedImageIndex = currentlySelectedImages.indexOf(image.id);
            if (selectedImageIndex > -1) {
                // Already selected. Second click de-selects.
                delete currentlySelectedImages[selectedImageIndex];
            } else if (!this.props.multiSelect) {
                currentlySelectedImages = [image.id];
            } else {
                currentlySelectedImages.push(image.id);
            }
            this.setState({
                selectedImages: currentlySelectedImages
            });
            if (this.props.onImageSelected) {
                this.props.onImageSelected(image, currentlySelectedImages);
            }
        },
        render: function() {
            return (
                <div className={"react-image-picker " + this.props.className} title={this.props.title}>
                    {this.props.images.map(function(image) {
                        var className = this.state.selectedImages.indexOf(image.id) > -1 ? 'selected' : '';
                        return (
                            <div className={"react-image-picker-image " + className}
                                 onClick={this.imageSelected.bind(this, image)}
                            >
                                <img src={image.url} />
                            </div>
                        );
                    }.bind(this))}
                </div>
            );
        }
    });

    return ImagePickerComponent;
});