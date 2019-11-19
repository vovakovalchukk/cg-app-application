import React, {useState} from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";
import Select from "Common/Components/Select";
import Checkbox from "Common/Components/Checkbox--stateless"
import {encodeData} from "Common/Utils/xhr/urlEncoder"
import fileDownload from "Common/Utils/xhr/fileDownload"
import ajax from 'Common/Utils/xhr/ajax';

const exportUrl = '/dataExchange/stock/export/download';

const StockExportApp = (props) => {
    const {templateOptions, actionOptions} = props;

    const templateState = useSelectState({});
    const sendViaEmailState = useCheckboxState(false);

    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);

    const onSubmit = async (event) => {
        event.preventDefault();
        const data = {
            templateId: templateState.selectedOption.value,
            sendViaEmail: sendViaEmailState.value
        };

        if (!data.sendViaEmail) {
            await exportToBrowser(data);
            return;
        }

        exportViaEmail(data)
    };

    return (
        <div>
            <div className="u-margin-top-xxlarge u-form-width-medium">
                <form id={"stock-import-form"} onSubmit={onSubmit} action={exportUrl}>
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
                                classNames={'u-inline-block u-width-120px'}
                            />
                        </div>
                    </div>
                    <div className="u-flex-v-center u-margin-top-small">
                        <label htmlFor="sendViaEmal" className="u-flex-1">Send via email:</label>
                        <div className="u-flex-4">
                            <Checkbox
                                id={"sendViaEmail"}
                                name={"sendViaEmail"}
                                onSelect={sendViaEmailState.onSelect}
                                isSelected={sendViaEmailState.value}
                            />
                        </div>
                    </div>

                    <button type="submit" className={'u-margin-top-med button'}>Download</button>
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

    async function exportToBrowser(data) {
        n.notice('We are exporting your stock...');
        const date = new Date();
        let fileDownloadResponse = await fileDownload.downloadBlob({
            url: exportUrl,
            data,
            desiredFilename:`stock-${date.toISOString().slice(0,10)}_${date.getTime()}.csv';`
        });

        if(fileDownloadResponse.status !== 200) {
            n.error('There was a problem exporting your stock. Please contact support for assistance.');
            return;
        }

        n.success('Successfully exported your stock.');
    }

    function exportViaEmail(data) {
        n.notice('We are processing your request...');
        ajax.request({
            method: 'POST',
            url:  exportUrl,
            data,
            onSuccess: ()=>{
                n.success("Please check your email for your stock export.")
            },
            onError: ()=>{
                n.error('There was a problem exporting your stock. Please contact support for assistance.')
            }
        });
    }
};

export default StockExportApp;

function useSelectState(initialValue) {
    const [selectedOption, setSelectedOption] = useState(initialValue);
    const onOptionChange = (newValue) => {
        setSelectedOption(newValue);
    };
    return {
        onOptionChange,
        selectedOption
    }
}

function useCheckboxState(initialValue) {
    const [value, setValue] = useState(initialValue);
    const onSelect = () => {
        setValue(!value);
    };
    return {
        onSelect,
        value,
        setValue
    };
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