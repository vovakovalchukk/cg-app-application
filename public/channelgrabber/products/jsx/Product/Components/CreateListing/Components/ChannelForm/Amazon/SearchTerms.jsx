import React from 'react';
import TextArea from 'Common/Components/TextArea';

const SearchTerms = (field) => {
    return <fieldset className="input-container">
        <span className={"inputbox-label"}>{field.displayTitle}</span>
        <div className={"order-inputbox-holder"}>
            <TextArea
                {...field.input}
                className={"textarea-description"}
            />
        </div>
    </fieldset>;
};

export default SearchTerms;