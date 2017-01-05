define([
    'react',
    'Common/IdGenerator',
    'EmailDesigner/Components/Elements/Text'
], function(
    React,
    IdGenerator,
    Text
) {
    "use strict";

    var ElementList = function() {
        this.elements = {
            'Text': function (elementData) {
                return (<Text id={elementData.id}
                              onElementSelected={elementData.onElementSelected}
                              text={elementData.text}
                              initialPosition={{x: elementData.x, y: elementData.y}}
                              size={{width: elementData.width, height: elementData.height}}
                />);
            }
        };

        this.defaults = {
            'Text': function () {
                return {
                    type: 'Text',
                    text: "Enter text...",
                    id: String(IdGenerator.generate()),
                    width: 200,
                    height: 50,
                    x: 0,
                    y: 0,
                    style: {
                        left: 0,
                        top: 0,
                        fontColour: "black",
                        fontFamily: "Arial",
                        fontSize: 12,
                    },
                    inspectors: [
                        'Delete',
                        'Text'
                    ]
                };
            }
        }
    };

    ElementList.prototype.renderElement = function (elementData) {
        return this.elements[elementData.type](elementData);
    };

    ElementList.prototype.getDefaultData = function (elementType) {
        return this.defaults[elementType]();
    };

    return new ElementList;
});