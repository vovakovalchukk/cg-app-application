import React, {useState} from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";
//import fileDownloadService from "Common/"
import fileDownload from 'Common/Utils/xhr/fileDownload';
import Select from 'Common/Components/Select';
console.log('fileDownload: ', fileDownload);

const App = (props) => {
    const {templateOptions, actionOptions} = props;
    const templateState = useSelectState({});
    const actionState = useSelectState({});

    const upload = () => {
        console.log(' in upload');
        const data = {
            templateId: templateState.selected.value,
            action: actionState.selected.value,
        };

        fileDownload.downloadBlob()
    };

    const formattedTemplateOptions = Object.keys(templateOptions).map((key) => {
        let templateValue = templateOptions[key];
        return {
            title: templateValue,
            name: templateValue
        };
    });

    const formattedActionOptions = Object.keys(actionOptions).map((value) => {
        let optionName = actionOptions[value];
        return {
            name: optionName,
            value
        };
    });

    return (<div>
        <form action="" method="post">
            <fieldset>
                <div>
                    <input
                        type="file"
                        id="docpicker"
                        accept=".text,.csv, .txt"
                        onChange={e => {
                            console.log('on inpuit chage', e)
                        }}
                    />
                </div>


                <div className="u-flex-v-center">
                    <label for="template" className="u-flex-1">Template</label>
                    <Select
                        id={"template"}
                        options={formattedTemplateOptions}
                        filterable={formattedTemplateOptions.length > 20}
                        {...templateState}
                    />
                </div>

                <div className="u-flex-v-center">
                    <label for="action" className="u-flex-1">Import Action</label>
                    <Select
                        id={"action"}
                        options={formattedActionOptions}
                        filterable={formattedActionOptions.length > 20}
                        onChange={(e) => {
                            console.log('in select onchange');
                        }}
                        {...actionState}
                    />
                </div>

                <input className={"button"} type="submit" value="Save"/>
            </fieldset>
        </form>

        <Table
            {...props}
            buildEmptySchedule={Service.buildEmptySchedule}
            columns={Service.getColumns()}
            formatPostDataForSave={Service.formatPostDataForSave}
            validators={Service.validators()}
        />
    </div>)
};

function useSelectState(initialValue){
    const [selected, setSelected] = useState(initialValue);

    const onChange = (e) => {
        const newValue = e.target.value;
        setSelected(newValue);
    };

    return {
        onChange,
        selected
    }
}


export default App;


