const Validators = {
    name: (schedule) => schedule.name.toString().trim().length > 2,
    template: (schedule) => !!schedule.templateId,
    toAccount: (schedule) => {
        if (!schedule.toDataExchangeAccountType) {
            return false;
        }

        if (!schedule.toDataExchangeAccountId) {
            return false;
        }

        if (schedule.toDataExchangeAccountType == 'email' && !schedule.fromDataExchangeAccountId) {
            return false;
        }

        return true;
    },
    fromAccountIdOnly: (schedule) => !!schedule.fromDataExchangeAccountId,
    filename: (schedule) => schedule.filename.toString().trim().length > 2,
    frequency: (schedule) => {
        if (!schedule.frequency) {
            return false;
        }

        const isHourValid = (schedule) => {
            return schedule.hour !== null && schedule.hour !== undefined && Number.isInteger(schedule.hour);
        };

        switch (schedule.frequency) {
            case 'hourly':
                return true;
            case 'daily':
                return isHourValid(schedule);
            case 'weekly':
                return schedule.day !== null && schedule.day !== undefined && schedule.day > 0
                    && isHourValid(schedule);
            case 'monthly':
                return schedule.date !== null && schedule.date !== undefined && schedule.date > 0
                    && isHourValid(schedule);
            default:
                return false;
        }
    },
    savedFilter: (schedule) => !!schedule.savedFilterName,
    action: (schedule) => !!schedule.action
};

export default Validators;