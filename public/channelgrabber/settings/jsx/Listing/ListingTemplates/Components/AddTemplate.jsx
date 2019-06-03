import React from 'react';
import Input from 'Common/Components/Input';
import FieldWrapper from 'Common/Components/FieldWrapper';
import * as PropTypes from "prop-types";

const AddTemplate = function(props) {
    return (
       <fieldset className={"u-margin-top-small"}>
            <span>
                <FieldWrapper label={"Add Template"}>
                    <Input
                        {...props.newTemplateName}
                        classNames={'u-inline-block'}
                    />
                    <button title={"Add Template"}
                            onClick={props.onAddClick}
                            className={'button u-margin-left-small'}
                    >
                        new
                    </button>
                </FieldWrapper>
            </span>
       </fieldset>
    );
}

AddTemplate.propTypes = {
    newTemplateName: PropTypes.shape({onChange: PropTypes.func, setValue: PropTypes.any, value: PropTypes.string}),
    onAddClick: PropTypes.func,
};

AddTemplate.defaultProps = {
    onClick: () => {},
    newTemplateName: {}
};

export default AddTemplate;