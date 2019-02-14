import React from 'react';

class Checkbox extends React.Component {
    defaultProps = {
        name: null,
        onChange: null
    };

    constructor(props) {
        super(props);
        this.state = {checked: props.checked || false};
    }

    toggle() {
        this.setState(
            {checked: !this.state.checked},
            () => {
                if (typeof this.props.onChange === "function") {
                    this.props.onChange(this.checked());
                }
            }
        )
    }

    checked() {
        return this.state.checked;
    }

    render() {
        return (
            <div className="checkbox-holder" onClick={() => this.toggle()}>
                <a className="std-checkbox">
                    <input type="checkbox" name={this.props.name} checked={this.checked()} />
                    <label>
                        <span className="checkbox_label">&nbsp;</span>
                    </label>
                </a>
            </div>
        );
    }
}

export default Checkbox;