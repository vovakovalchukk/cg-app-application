import React from 'react';
import ButtonLink from 'MessageCentre/Components/ButtonLink';

const ThreadNavigator = (props) => {
    const {prev, next, thread, of} = props;
    return (
        <div className={`u-display-flex`}>
            <ButtonLink
                to={prev}
                text={`<`}
                title={`Previous thread`}
            />

            <p>{thread} / {of}</p>

            <ButtonLink
                to={next}
                text={`>`}
                title={`Next thread`}
            />
        </div>
    );
};

export default ThreadNavigator;