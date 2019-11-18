import React, {useState} from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";
import Select from "DataExchange/StockImport/App";
import ajax from 'Common/Utils/xhr/ajax';

const StockExportApp = (props) => {
    const {templateOptions, actionOptions} = props;

    const templateState = useSelectState({});
    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);

    const onSubmit = (event) => {
//        ajax.request({
//            method: 'POST',
//            url:  importUrl,
//            data:  {
//                templateId: templateState.selectedOption.value,
//                action: actionState.selectedOption.value,
//                uploadFile: csv
//            },
//            onSuccess: showSuccessNoticeForSubmit,
//            onError: showErrorNoticeForSubmit
//        });
//        event.preventDefault();
    };


    return (
        <div>
            <form id={"stock-import-form"} onSubmit={onSubmit}>
                <div className="u-flex-v-center u-margin-top-small">
                    <label htmlFor="template" className="u-flex-1">Template</label>
                    <div className="u-flex-4">
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
                </div>
                <button type="submit" className={'u-margin-top-med button'}>Search</button>
            </form>

            <Table
                {...props}
                buildEmptySchedule={Service.buildEmptySchedule}
                columns={Service.getColumns()}
                formatPostDataForSave={Service.formatPostDataForSave}
                validators={Service.validators()}
            />
        </div>
    );
};

export default StockExportApp;

function useSelectState(initialValue){
    const [selectedOption, setSelectedOption] = useState(initialValue);
    const onOptionChange = (newValue) => {
        setSelectedOption(newValue);
    };
    return {
        onOptionChange,
        selectedOption
    }
}

function formatOptionsFromMap(map) {
    console.log('map in formatOptionsFromMap: ', map);

    return Object.keys(map).map((key) => {
        return {
            title: map[key],
            name: map[key],
            value: key
        };
    });
}