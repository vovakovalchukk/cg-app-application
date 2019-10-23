import React from 'react';
import styled from "styled-components";

const IconContainer = styled.span`
    cursor: ${props => props.disabled ? 'not-allowed' : 'pointer'};
    color: ${props => props.disabled ? 'lightgrey' : 'black'};
    margin-right: 10px;
    visibility: ${props => props.isHidden ? 'hidden' : 'visible'};
`;

const Actions = (props) => {

    const renderSaveIcon = () => {
        return <IconContainer disabled={props.saveIconDisabled}>
            <i
                className={'fa fa-2x fa-check-square-o'}
                aria-hidden="true"
                onClick={() => {!props.saveIconDisabled ? props.onSave() : null}}
                title={'Save'}
            />
        </IconContainer>;
    };

    const renderRemoveIcon = () => {
        return <IconContainer isHidden={!props.removeIconVisible}>
            <i
                className={'fa fa-2x fa-trash-o'}
                aria-hidden="true"
                onClick={props.onDelete}
                title={'Delete'}
            />
        </IconContainer>;
    };

    return <span>
        {renderSaveIcon()}
        {renderRemoveIcon()}
    </span>;
};

Actions.defaultProps = {
    removeIconVisible: true,
    saveIconDisabled: false,
    onSave: () => {},
    onDelete: () => {}
};

export default Actions;
