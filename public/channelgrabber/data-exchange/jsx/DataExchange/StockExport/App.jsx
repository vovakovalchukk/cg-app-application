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

    const onSubmit = (event) => {
        console.log('event: ', event);
        const data = {
            templateId: "1"
            sendViaEmail: sendViaEmailState.value
        };

        if (!data.sendViaEmail) {
            fileDownload.downloadBlob({
                url: exportUrl,
                data,
                desiredFilename:`stock-${new Date().toISOString().slice(0,10)}.csv';`
            });
            return;
        }

        ajax.request({
            method: 'POST',
            url:  exportUrl,
            data:  {
                templateId: templateState.selectedOption.value,
            },
            onSuccess: (data)=>{console.log('in success',data)},
            onError: ()=>{console.log('in error')}
        });
        event.preventDefault();
    };

    return (
        <div>
            <div className="u-margin-top-xxlarge u-form-width-medium">
                <form id={"stock-import-form"} onSubmit={onSubmit} action={exportUrl}>
                    {/*<div className="u-flex-v-center u-margin-top-small">*/}
                        {/*<label htmlFor="template" className="u-flex-1">Template:</label>*/}
                        {/*<div className="u-flex-4">*/}
                            {/*<Select*/}
                                {/*id={"template"}*/}
                                {/*name={"template"}*/}
                                {/*options={formattedTemplateOptions}*/}
                                {/*filterable={true}*/}
                                {/*autoSelectFirst={false}*/}
                                {/*selectedOption={templateState.selectedOption}*/}
                                {/*onOptionChange={templateState.onOptionChange}*/}
                                {/*classNames={'u-inline-block u-width-120px'}*/}
                            {/*/>*/}
                        {/*</div>*/}
                    {/*</div>*/}
                        {/*<input type="text" name="templateId" placeholder="Email" value={"1"} />*/}
                    {/*<div className="u-flex-v-center u-margin-top-small">*/}
                        {/*<label htmlFor="sendViaEmal" className="u-flex-1">Send via email:</label>*/}
                        {/*<div className="u-flex-4">*/}
                            {/*<Checkbox*/}
                                {/*id={"sendViaEmail"}*/}
                                {/*name={"sendViaEmail"}*/}
                                {/*onSelect={sendViaEmailState.onSelect}*/}
                                {/*isSelected={sendViaEmailState.value}*/}
                            {/*/>*/}
                        {/*</div>*/}
                    {/*</div>*/}

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