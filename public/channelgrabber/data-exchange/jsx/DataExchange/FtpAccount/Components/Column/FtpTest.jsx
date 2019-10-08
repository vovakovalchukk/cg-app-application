import React from 'react';
import styled from "styled-components";

class FtpTestColumn extends React.Component {
    static defaultProps = {
        onClick: () => {}
    };

    render() {
        return (
            <div className="button" onClick={this.props.onClick}>
                <i className="fa fa-2x fa-wifi" aria-hidden="true"/>
                <span className="button-text">Test connection</span>
            </div>
        );
    }
}

export default FtpTestColumn;
