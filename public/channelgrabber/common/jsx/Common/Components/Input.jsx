define([
    'react'
], function(
    React
) {
    "use strict";

    var InputComponent = React.createClass({
        getDefaultProps: function() {
            return {
                inputType: 'input',
                title: null,
                errors: []
            }
        },
        renderErrors: function() {
            if (this.props.errors.length == 0) {
                return;
            }

            return <ul className={'errors-input'}>
                {this.props.errors.map(function(error) {
                    return <li>{error}</li>;
                })}
            </ul>;
        },
        render: function () {
            console.log('in Input with props: ',this.props);
            return (
                <div className="safe-input-box">
                    <input
                        type={this.props.inputType}
                        name={this.props.name}
                        value={this.props.value}
                        onChange={this.props.onChange}
                        title={this.props.title}
                        className={this.props.errors.length == 0 ? '' : 'safe-input-box--error'}
                    />
                    {this.renderErrors()}
                </div>
            );
        }
    });

    return InputComponent;
});
