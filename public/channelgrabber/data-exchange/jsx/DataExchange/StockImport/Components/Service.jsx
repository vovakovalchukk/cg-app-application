import Columns from "DataExchange/Schedule/Columns";

const Service = {
    buildEmptySchedule: () => {
        return {
            active: false,
            date: 1,
            day: 1,
            filename: '{{type}}-{{date}}-{{time}}.csv',
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
    isScheduleValid: (schedule) => {
        if (schedule.name.toString().trim().length < 2) {
            return false;
        }

        if (!schedule.templateId) {
            return false;
        }

        if (!schedule.action) {
            return false;
        }

        if (!schedule.fromDataExchangeAccountId) {
            return false;
        }

        if (schedule.filename.toString().trim().length < 2) {
            return false;
        }

        if (!schedule.frequency) {
            return false;
        }

        return true;
    }
};

export default Service;
