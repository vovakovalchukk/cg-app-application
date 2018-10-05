import React from 'react';


class SearchComponent extends React.Component {
    static defaultProps = {
        onSubmit: function () {},
        disabled: false
    };

    state = {
        value: ''
    };

    componentWillReceiveProps(nextProps) {
        if (!nextProps.value) {
            return;
        }
        this.setState({
            value: nextProps.value
        });
    }

    onChange = (event) => {
        this.setState({
            value: event.target.value
        });
    };

    onSubmit = (event) => {
        event.preventDefault();
        this.props.onSubmit(this.state.value);
    };

    render() {
        return (
            <span className={"heading-large"}>
                <label>Existing category maps</label>
                <form
                    className="search-form"
                    name="search"
                    onSubmit={this.onSubmit}
                >
                    <label>
                        <div className={"order-inputbox-holder"}>
                            <input
                                type="text"
                                placeholder="Search..."
                                onChange={this.onChange}
                                value={this.state.value}
                                disabled={this.props.disabled}
                            />
                        </div>
                    </label>
                </form>
            </span>
        );
    }
}

export default SearchComponent;
