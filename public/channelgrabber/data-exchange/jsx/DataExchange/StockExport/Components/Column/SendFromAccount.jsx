import React from 'react';
import SelectComponent from "Common/Components/Select";

const SendFromAccount = (props) => {

    const formatOptions = () => {
        return Object.keys(props.fromAccountOptions).map((templateId) => {
            return {
                value: templateId,
                name: props.fromAccountOptions[templateId]
            }
        });
    };

    const findSelectedOption = () => {
        const account = Object.keys(props.fromAccountOptions).find((accountId) => {
            return accountId == props.schedule.fromDataExchangeAccountId;
        });

        return {
            value: account,
            name: props.fromAccountOptions[account]
        }
    };

    const onOptionChange = (option) => {
        props.onChange(option.value);
    };

    return <span>
        <SelectComponent
            options={formatOptions()}
            selectedOption={findSelectedOption()}
            onOptionChange={onOptionChange}
            autoSelectFirst={false}
        />
    </span>;
};

SendFromAccount.defaultProps = {
    schedule: {},
    index: 0,
    fromAccountOptions: {},
    onChange: () => {}
};

export default SendFromAccount;
