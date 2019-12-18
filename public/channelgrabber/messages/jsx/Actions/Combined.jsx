import statusActions from 'MessageCentre/Actions/statusActions';
import messageActions from 'MessageCentre/Actions/messageActions';
import columnActions from 'MessageCentre/Actions/columnActions';
import searchActions from 'MessageCentre/Actions/searchActions';

export default () => {
    return {
        ...statusActions,
        ...messageActions,
        ...columnActions,
        ...searchActions
    };
};
