import React from 'react';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import styled from 'styled-components';

const StyledDiv = styled.div`
    align-items: center;
`;

const ThreadNavigator = (props) => {
    const {prev, next, thread, of} = props;
    return (
        <StyledDiv className={`u-display-flex`}>
            <ButtonLink
                to={prev}
                sprite={`sprite-arrow-left-16-grey`}
                title={`Previous thread`}
            />

            <div className={`u-margin-left-small u-margin-right-small`}>{thread} of {of}</div>

            <ButtonLink
                to={next}
                sprite={`sprite-arrow-right-16-grey`}
                title={`Next thread`}
            />
        </StyledDiv>
    );
};

export default ThreadNavigator;