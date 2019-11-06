import React, {useState} from 'react';
import Select from 'Common/Components/Select';

const importUrl = "/dataExchange/stock/import/upload";

const App = (props) => {
    const {templateOptions, actionOptions} = props;
    const templateState = useSelectState({});
    const actionState = useSelectState({});

    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);
    const formattedActionOptions = formatOptionsFromMap(actionOptions);
    const [csv, setCsv] = useState();

    const onSubmit = () => {
        const request = new XMLHttpRequest();
        request.open('POST', importUrl, true);

        request.onload = function() {
            if (request.status.toString()[0] !== "2") {
                showErrorNoticeForSubmit();
                return;
            }
            n.success('You have successfully imported your stock.')
        };
        request.onerror = function() {
            showErrorNoticeForSubmit();
        };

        const formData = {
            templateId: templateState.selectedOption.value,
            action: actionState.selectedOption.value,
            uploadFile: csv
        };

        request.send(formData);
        event.preventDefault();
    };
    
    const onFileUpload = (e) => {
        const files = Array.from(e.target.files);
        const reader = new FileReader();

        reader.readAsBinaryString(files[0]);
        reader.addEventListener('load', function (e) {
            setCsv(e.target.result);
        });
    };
    
    return (
        <div class="u-margin-top-xxlarge" style={{width:'500px'}}>
            <form id={"stock-import-form"} onSubmit={onSubmit}>
                <div className="u-flex-v-center">
                    <input
                        type="file"
                        name="uploadFile"
                        id="uploadFile"
                        accept=".text,.csv, .txt"
                        onChange={onFileUpload}
                    />
                </div>
                <div className="u-flex-v-center">
                    <label htmlFor="template" className="u-flex-1">Template</label>
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

function showErrorNoticeForSubmit() {
    n.error('There was an error processing your request. Please contact support for further information.');
}

export default App;


