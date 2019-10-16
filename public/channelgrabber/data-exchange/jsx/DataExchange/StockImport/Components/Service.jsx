import Columns from "DataExchange/Schedule/Columns";

const Service = {
    buildEmptySchedule: () => {
        return {
            active: false,
            date: null,
            day: null,
            filename: '{{type}}-{{date}}-{{time}}.csv',
            frequency: 'hourly',
            fromDataExchangeAccountId: null,
            fromDataExchangeAccountType: null,
            hour: null,
            id: null,
            name: '',
            operation: 'export',
            templateId: null,
            toDataExchangeAccountId: null,
            toDataExchangeAccountType: null
        }
    },
    getColumns: () => {
        return [
            Columns.enabled,
            Columns.ruleName,
            Columns.template,
            Columns.importAction,
            Columns.receiveFrom,
            Columns.fileName,
            Columns.frequency,
            Columns.when,
            Columns.actions,
        ];
    },
    formatPostDataForSave: (schedule) => {
        const postData = {...schedule};

        delete postData.fromDataExchangeAccountType;
        delete postData.toDataExchangeAccountId;
        delete postData.toDataExchangeAccountType;

        if (!postData.id) {
            delete postData.id
        }

        return postData;
    },
};

export default Service;
