import React from 'react';
import SelectComponent from "Common/Components/Select";

const SendToAccount = (props) => {

    const formatOptions = () => {
        return Object.keys(props.toAccountOptions).map((templateId) => {
            return {
                value: templateId,
                name: props.toAccountOptions[templateId]
            }
        });
    };

    const findSelectedOption = () => {
        const account = Object.keys(props.toAccountOptions).find((account) => {
            const [accountType, accountId] = account.split('-');
            return accountId == props.schedule.toDataExchangeAccountId
                && accountType == props.schedule.toDataExchangeAccountType;
        });

        return {
            value: account,
            name: props.toAccountOptions[account]
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

SendToAccount.defaultProps = {
    schedule: {},
    toAccountOptions: {},
    onChange: () => {}
};

export default SendToAccount;
