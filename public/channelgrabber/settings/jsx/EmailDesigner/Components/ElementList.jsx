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
                return (<Text id={elementData.id} text={elementData.text} initialPosition={{x: elementData.x, y: elementData.y}}/>);
            }
        };

        this.defaults = {
            'Text': function () {
                return {
                    type: 'text',
                    text: "Enter text...",
                    id: String(IdGenerator.generate()),
                    width: 200,
                    height: 50,
                    style: {
                        left: 0,
                        top: 0,
                        fontColour: "black",
                        fontFamily: "Arial",
                        fontSize: 12,
                    }
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