import React from 'react';

const CharacterCounter = (props) => {
    if (!props.stringToEvaluate) {
        return null;
    }
    const stringLength = props.stringToEvaluate.length;
    return (
        <div className={props.className}>
            {stringLength}
        </div>
    )
};

export default CharacterCounter;