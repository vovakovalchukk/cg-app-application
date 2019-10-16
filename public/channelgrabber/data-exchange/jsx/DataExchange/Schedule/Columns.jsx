const KEY_ENABLED = 'enabled';
const KEY_RULE_NAME = 'rule_name';
const KEY_TEMPLATE = 'template';
const KEY_SEND_TO = 'send_to';
const KEY_SEND_FROM = 'send_from';
const KEY_FILE_NAME = 'file_name';
const KEY_FREQUENCY = 'frequency';
const KEY_WHEN = 'when';
const KEY_ACTIONS = 'actions';

const Columns = {
    enabled: {
        header: 'Enabled',
        width: '80px'
    },
    ruleName: {
        header: 'Rule name',
    },
    template: {
        header: 'Template',
    },
    sendTo: {
        header: 'Send to',
        width: '250px'
    },
    sendFrom: {
        header: 'Send from',
        width: '250px'
    },
    fileName: {
        header: 'File name',
    },
    frequency: {
        header: 'Frequency',
    },
    when: {
        header: 'When',
        width: '230px'
    },
    actions: {
        header: 'Actions',
        width: '80px'
    },
};

export default Columns;
