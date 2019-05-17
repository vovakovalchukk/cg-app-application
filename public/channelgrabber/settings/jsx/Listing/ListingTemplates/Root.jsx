import React, {useState, useReducer} from 'react';

import LoadTemplate from 'Settings/jsx/Listing/ListingTemplates/Components/LoadTemplate';
import NameTemplate from 'Settings/jsx/Listing/ListingTemplates/Components/NameTemplate';
import NewTemplate from 'Settings/jsx/Listing/ListingTemplates/Components/NewTemplate';

import templateReducer from 'Settings/jsx/Listing/ListingTemplates/Reducers/template';
import templateActions from 'Settings/jsx/Listing/ListingTemplates/Actions/template';

const initialState = {
    initialised: false,
    templateName: '',
    selectedLoadTemplateOption: {},
    editorHtml: '',
    newTemplateName : '',
    userTemplates: []
};

let RootComponent = props => {
    const [state, dispatch] = useReducer(templateReducer, initialState);


    // todo put this behind a use effect to achieve the similar effect of ComponentDidMount. Look at notes for details.
    dispatch(templateActions.storeUserTemplates(props.tempalates));

    console.log('this.props: ',       props);
    
    
    return (
        <div>
            <LoadTemplate
                onOptionChange={option => {
                    dispatch(templateActions.changeLoadTemplateOption(option))
                }}
                selectedOption={state.selectedLoadTemplateOption}
            />

            <NameTemplate
                onChange={(e) => {
                    dispatch(templateActions.changeTemplateName(e))
                }}
                value={state.templateName}
                shouldShow={
                    state.initialised
                }
            />

            <NewTemplate
                onChange={(e) => {
                    dispatch(templateActions.changeNewTemplateName(e))
                }}
                value={state.newTemplateName}
            />
            <button onClick={()=>{dispatch(templateActions.addNewTemplate)}}>new</button>
        </div>
    );
};

export default RootComponent;