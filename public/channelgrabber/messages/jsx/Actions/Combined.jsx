import filterActions from 'MessageCentre/Actions/filterActions';
import messageActions from 'MessageCentre/Actions/messageActions';
import columnActions from 'MessageCentre/Actions/columnActions';
import searchActions from 'MessageCentre/Actions/searchActions';
import templateActions from 'MessageCentre/Actions/templateActions';

export default () => {
    return {
        ...filterActions,
        ...messageActions,
        ...columnActions,
        ...searchActions,
        ...templateActions
    };
};