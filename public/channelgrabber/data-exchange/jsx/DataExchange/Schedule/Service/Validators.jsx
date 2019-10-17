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

        return scheduleValidationMap[schedule.frequency](schedule);
    },
    savedFilter: (schedule) => !!schedule.savedFilterName,
    action: (schedule) => !!schedule.action
};

export default Validators;

const scheduleValidationMap = {
    'hourly': () => true,
    'daily': (schedule) => isHourValid(schedule),
    'weekly': (schedule) => isDayValid(schedule) && isHourValid(schedule),
    'monthly': (schedule) => isDateValid(schedule) && isHourValid(schedule)
};

const isHourValid = (schedule) => {
    return schedule.hour !== null && schedule.hour !== undefined && Number.isInteger(schedule.hour);
};

const isDayValid = (schedule) => {
    return schedule.day !== null && schedule.day !== undefined && schedule.day > 0;
};

const isDateValid = (schedule) => {
    return schedule.date !== null && schedule.date !== undefined && schedule.date > 0;
};
