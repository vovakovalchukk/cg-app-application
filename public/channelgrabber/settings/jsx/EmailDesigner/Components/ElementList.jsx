define([
    'react',
    'EmailDesigner/Components/Elements/Text'
], function(
    React,
    Text
) {
    "use strict";

    var ElementList = function() {
        this.elements = {
            'Text': function (elementData) {
                return (<Text id={elementData.id} text={elementData.text} initialPosition={{x: elementData.x, y: elementData.y}}/>);
            }
        }
    };

    ElementList.prototype.createElement = function (elementData) {
        return this.elements[elementData.type](elementData);
    };

    return new ElementList;
});