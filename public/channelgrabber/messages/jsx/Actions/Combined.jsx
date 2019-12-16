import filterActions from 'MessageCentre/Actions/filterActions';
import messageActions from 'MessageCentre/Actions/messageActions';
import columnActions from 'MessageCentre/Actions/columnActions';

export default () => {
    return {
        ...filterActions,
        ...messageActions,
        ...columnActions
    };
};
