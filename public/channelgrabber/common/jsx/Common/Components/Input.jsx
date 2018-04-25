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
                errors: [],
                classNames:'',
                disabled:null,
                onChange:null
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
        getClassNames: function(){
            var classNames = this.props.classNames;
            classNames += (this.props.errors.length == 0 ? '' : ' safe-input-box--error ');
            classNames += (this.props.disabled ? ' safe-input-box--disabled ' : '');
           return classNames;
        },
        render: function () {
            return (
                <div className={this.props.classNames + " safe-input-box"}>
                    <input
                        type={this.props.inputType}
                        name={this.props.name}
                        value={this.props.value}
                        onChange={this.props.onChange}
                        title={this.props.title}
                        className={ this.getClassNames()}
                        disabled={ (this.props.disabled ? 'disabled' : '') }
                    />
                    {this.renderErrors()}
                </div>
            );
        }
    });

    return InputComponent;
});
