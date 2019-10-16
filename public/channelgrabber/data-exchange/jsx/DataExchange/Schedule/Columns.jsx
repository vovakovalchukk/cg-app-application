const KEY_ENABLED = 'enabled';
const KEY_RULE_NAME = 'rule_name';
const KEY_TEMPLATE = 'template';
const KEY_SEND_TO = 'send_to';
const KEY_SEND_FROM = 'send_from';
const KEY_FILE_NAME = 'file_name';
const KEY_FREQUENCY = 'frequency';
const KEY_WHEN = 'when';
const KEY_ACTIONS = 'actions';
const KEY_RECEIVE_FROM = 'receive_from';
const KEY_IMPORT_ACTION = 'import_action';

const Columns = {
    enabled: {
        header: 'Enabled',
        width: '80px',
        key: KEY_ENABLED,
    },
    ruleName: {
        header: 'Rule name',
        key: KEY_RULE_NAME,
    },
    template: {
        header: 'Template',
        key: KEY_TEMPLATE
    },
    sendTo: {
        header: 'Send to',
        width: '250px',
        key: KEY_SEND_TO,
    },
    sendFrom: {
        header: 'Send from',
        width: '250px',
        key: KEY_SEND_FROM,
    },
    fileName: {
        header: 'File name',
        key: KEY_FILE_NAME,
    },
    frequency: {
        header: 'Frequency',
        key: KEY_FREQUENCY,
    },
    when: {
        header: 'When',
        width: '230px',
        key: KEY_WHEN,
    },
    actions: {
        header: 'Actions',
        width: '80px',
        key: KEY_ACTIONS
    },
    receiveFrom: {
        header: 'Receive From',
        width: '250px',
        key: KEY_RECEIVE_FROM
    },
    importAction: {
        header: 'Import Action',
        width: '150px',
        key: KEY_IMPORT_ACTION
    }
};

export default Columns;
export {
    KEY_ACTIONS,
    KEY_WHEN,
    KEY_FREQUENCY,
    KEY_FILE_NAME,
    KEY_SEND_FROM,
    KEY_SEND_TO,
    KEY_TEMPLATE,
    KEY_RULE_NAME,
    KEY_ENABLED,
    KEY_RECEIVE_FROM,
    KEY_IMPORT_ACTION
};
