import React from 'react';


class LoadMoreButton extends React.Component {
    static defaultProps = {
        onClick: function () {},
        disabled: false,
        active: false
    };

    onClick = () => {
        if (this.props.disabled) {
            return;
        }
        this.props.onClick();
    };

    getClassName = () => {
        return "button container-btn yes" + (this.props.disabled ? " disabled" : "");
    };

    render() {
        if (!this.props.active) {
            return null;
        }
        return (
            <span className="button-container">
                <div className="load-more-button">
                    <div className={this.getClassName()} onClick={this.onClick}>
                        <span>Load more</span>
                    </div>
                </div>
            </span>
        );
    }
}

export default LoadMoreButton;
