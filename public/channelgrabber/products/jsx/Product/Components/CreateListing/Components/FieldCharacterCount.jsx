import CharacterCounter from './CharacterCounter';
import React from "react";

const FieldCharacterCount = (props) => {
    return (
        <div>
            {props.renderField()}
            <CharacterCounter className="u-margin-left-small u-margin-top-xsmall u-float-left" stringToEvaluate={props.value} />
        </div>
    )
};

export default FieldCharacterCount;