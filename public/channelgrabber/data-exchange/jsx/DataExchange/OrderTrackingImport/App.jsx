import React, {useState} from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";
import Select from "Common/Components/Select";
import ajax from "Common/Utils/xhr/ajax";

const importUrl = "/dataExchange/orderTracking/import/upload";

const OrderTrackingApp = (props) => {
    const {templateOptions} = props;
    const templateState = useSelectState({});

    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);
    const [csv, setCsv] = useState();

    const onSubmit = (event) => {
        ajax.request({
            method: 'POST',
            url:  importUrl,
            data:  {
                templateId: templateState.selectedOption.value,
                uploadFile: csv
            },
            onSuccess: showSuccessNoticeForSubmit,
            onError: showErrorNoticeForSubmit
        });
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
        <div>
            <div className="u-margin-top-xxlarge u-form-max-width-medium">
                <form id={"stock-import-form"} onSubmit={onSubmit}>
                    <div className="u-flex-v-center u-margin-top-small">
                        <input
                            type="file"
                            name="uploadFile"
                            id="uploadFile"
                            accept=".text,.csv, .txt"
                            onChange={onFileUpload}
                        />
                    </div>
                    <div className="u-flex-v-center u-margin-top-small">
                        <label htmlFor="template" className="u-flex-1">Template:</label>
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
                    <button
                        type="submit"
                        className={'u-margin-top-med button'}
                        disabled={!(templateState.selectedOption.value && csv)}
                    >
                        Upload
                    </button>
                </form>
            </div>
            <div className={'u-margin-top-medium u-inline-block'}>
                <Table
                    {...props}
                    buildEmptySchedule={Service.buildEmptySchedule}
                    columns={Service.getColumns()}
                    formatPostDataForSave={Service.formatPostDataForSave}
                    validators={Service.validators()}
                />
            </div>
        </div>
    );
};

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
    return Object.keys(map).map((key) => {
        return {
            title: map[key],
            name: map[key],
            value: key
        };
    });
}

function showSuccessNoticeForSubmit() {
    n.success('You have successfully imported your order tracking information.');
}

function showErrorNoticeForSubmit() {
    n.error('There was an error processing your request. Please contact support for further information.');
}

export default OrderTrackingApp;
