import Columns from "DataExchange/Schedule/Columns";
import Validators from "DataExchange/Schedule/Service/Validators";

const Service = {
    buildEmptySchedule: () => {
        return {
            active: false,
            date: 1,
            day: 1,
            filename: 'orderTracking.csv',
            frequency: 'hourly',
            fromDataExchangeAccountId: null,
            hour: 0,
            id: null,
            name: '',
            templateId: null,
        }
    },
    getColumns: () => {
        return [
            Columns.enabled,
            Columns.ruleName,
            Columns.template,
            Columns.receiveFrom,
            Columns.fileName,
            Columns.frequency,
            Columns.when,
            Columns.actions,
        ];
    },
    formatPostDataForSave: (schedule) => {
        const postData = {...schedule};

        delete postData.toDataExchangeAccountId;
        delete postData.toDataExchangeAccountType;

        if (!postData.id) {
            delete postData.id
        }

        return postData;
    },
    validators: () => {
        return [
            Validators.name,
            Validators.template,
            Validators.fromAccountIdOnly,
            Validators.filename,
            Validators.frequency
        ];
    }
};

export default Service;
