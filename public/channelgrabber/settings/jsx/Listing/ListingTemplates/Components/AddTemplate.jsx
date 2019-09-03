import React from 'react';
import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';
import * as PropTypes from "prop-types";

const AddTemplate = function(props) {
    return (
       <fieldset className={"u-margin-top-small"}>
            <span>
                <FieldWithLabel label={"Add Template"}>
                    <Input
                        {...props.newTemplateName}
                        classNames={'u-inline-block'}
                        inputClassNames={'inputbox u-border-box'}
                    />
                    <button title={"Add Template"}
                            onClick={props.onAddClick}
                            className={'button u-margin-left-small'}
                    >
                        New
                    </button>
                </FieldWithLabel>
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