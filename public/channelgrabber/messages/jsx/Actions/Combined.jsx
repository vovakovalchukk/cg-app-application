import filterActions from "MessageCentre/Actions/filterActions";
import messageActions from "MessageCentre/Actions/messageActions";

export default () => {
    return {
        ...filterActions,
        ...messageActions
    };
};
