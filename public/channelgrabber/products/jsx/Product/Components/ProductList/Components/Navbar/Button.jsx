import React from 'react';

const Button = (props) => {
    return (
        <span className={"navbar-strip__button u-margin-left-small"} onClick={props.onClick}>
            <span className={props.iconClass + " left icon icon--medium navbar-strip__button__icon"}>&nbsp;</span>
            <span className="navbar-strip__button__text">{props.buttonLabel}</span>
        </span>
    );
};

export default Button;