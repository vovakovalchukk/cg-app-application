import FieldWrapper from "Common/Components/FieldWrapper";
import Select from 'Common/Components/Select';
import * as PropTypes from "prop-types";
import React from "react";
import Input from "Settings/jsx/Listing/ListingTemplates/Components/AddTemplate";

function TemplateSelect(props) {
    console.log('props in templateSelect: ', props);

    return <FieldWrapper label={"Load Template"} className={"u-margin-top-small"}>
        <Select
            options={props.options}
            filterable={true}
            autoSelectFirst={false}
            title={"choose your template to load"}
            selectedOption={props.selectedOption}
            onOptionChange={props.onOptionChange}
            classNames={'u-inline-block'}
        />
        <button onClick={props.deleteTemplate} className={"u-margin-left-small"}>
            delete
        </button>
    </FieldWrapper>;
}

TemplateSelect.propTypes = {
    options: PropTypes.array,
    selectedOption: PropTypes.object,
    onOptionChange: PropTypes.func
};

TemplateSelect.defaultProps = {
    options: [],
    selectedOption: {},
    onOptionChange: () => {}
};

export default TemplateSelect;