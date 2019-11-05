import React, {useState} from 'react';
import Select from 'Common/Components/Select';

const App = (props) => {
    const {templateOptions, actionOptions} = props;
    const templateState = useSelectState({});
    const actionState = useSelectState({});

    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);
    const formattedActionOptions = formatOptionsFromMap(actionOptions);

    const onSubmit = (e) => {
        console.log('in on submit');
        const url = "/dataExchange/stock/import/upload";
        const request = new XMLHttpRequest();
        request.open('POST', url, true);
        request.onload = function() { // request successful
            console.log('got responseText', request);
            console.log(request.responseText);
        };
        request.onreadystatechange = function() {//Call a function when the state changes.
            console.log('request.readyState: ', request.readyState);
        };
        request.onerror = function() {
            // request failed
            console.log('failed requwst');
        };
        const formData = new FormData(e.target);
        formData.append('templateId', templateState.selectedOption.value);
        formData.append('action', actionState.selectedOption.value);

        //todo - remove this debug
        for(var pair of formData.entries()) {
            console.log('----------pair-----------');
            console.log('key: ', pair[0]);
            console.log('value: ', pair[1]);
        }

        request.send(formData);
        event.preventDefault();
    };
    
    const onFileUpload =   e => {
        const files = Array.from(e.target.files)
        console.log('files: ', files);
        
        
    };
    
    return (
        <div style={{width:'500px'}}>
            <form id={"stock-import-form"} onSubmit={onSubmit}>
                <input type="text" name="dummy" />
                <input
                    type="file"
                    name="uploadFile"
                    id="uploadFile"
                    accept=".text,.csv, .txt"
                    onChange={onFileUpload}
                />
                <div className="u-flex-v-center">
                    <label htmlFor="template" className="u-flex-1">Template</label>
                    {/*<Select*/}
                        {/*id={"template"}*/}
                        {/*options={formattedTemplateOptions}*/}
                        {/*filterable={formattedTemplateOptions.length > 20}*/}
                        {/*{...templateState}*/}
                    {/*/>*/}
                    <Select
                        id={"template"}
                        name={"template"}
                        options={formattedTemplateOptions}
                        filterable={true}
                        autoSelectFirst={false}
                        selectedOption={templateState.selectedOption}
                        onOptionChange={templateState.onOptionChange}
                        classNames={'u-inline-block'}
                    />
                </div>
                <div className="u-flex-v-center">
                    <label htmlFor="action" className="u-flex-1">Import Action</label>
                    <Select
                        id={"action"}
                        name={"action"}
                        options={formattedActionOptions}
                        filterable={true}
                        autoSelectFirst={false}
                        selectedOption={actionState.selectedOption}
                        onOptionChange={actionState.onOptionChange}
                        classNames={'u-inline-block'}
                    />
                </div>
                <button type="submit" >Search</button>
            </form>
        </div>
    );
};

function useSelectState(initialValue){
    const [selectedOption, setSelectedOption] = useState(initialValue);
    const onOptionChange = (newValue) => {
        console.log('newValue to set: ', newValue);
        setSelectedOption(newValue);
    };
    return {
        onOptionChange,
        selectedOption
    }
}

function formatOptionsFromMap(map) {
    return Object.keys(map).map((key) => {
        return {
            title: map[key],
            name: map[key],
            value: key
        };
    });
}

export default App;


