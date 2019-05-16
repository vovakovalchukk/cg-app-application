import React, {useState} from 'react';

import LoadTemplate from 'Settings/jsx/Listing/ListingTemplates/Components/LoadTemplate';
import NameTemplate from 'Settings/jsx/Listing/ListingTemplates/Components/NameTemplate';
import NewTemplate from 'Settings/jsx/Listing/ListingTemplates/Components/NewTemplate';

let RootComponent = props => {
    const templateName = useFormInput('');
    const newTemplateName = useFormInput('');
    const selectedTemplate = useSelectInput();

    return (
        <div>
            <LoadTemplate
                {...selectedTemplate}
            />

            <NameTemplate
                {...templateName}
            />

            <NewTemplate
                {...newTemplateName}
            />
        </div>
    );
};

export default RootComponent;

function useFormInput(initialValue) {
    const [value, setValue] = useState(initialValue);
    function onChange(e) {
        setValue(e.target.value);
    }
    return {
        value,
        onChange
    }
}

function useSelectInput() {
    const [value, setValue] = useState();
    function onChange(option) {
        setValue(option);
    }
    return {
        selectedOption: value,
        onOptionChange: onChange
    }
}