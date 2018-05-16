define([
    'react'
], function(
    React
) {
    var renderError = function(error) {
        return (
            <div className="o-container-wrap o-container-wrap--left u-color-red">
                {error}
            </div>
        );
    };
    var InputWithValidation = function(props) {
        return (<div>
            <div className="o-container-wrap">
                <input
                    name={props.input.name}
                    onBlur={props.input.onBlur}
                    onFocus={props.input.onFocus}
                    onChange={props.input.onChange}
                    onDragStart={props.input.onDragStart}
                    onDrop={props.input.onDrop}
                    value={props.input.value}
                    type={props.type}
                    className={"c-input-field"}
                />
            </div>
            {(props.meta.touched && props.meta.error) ? renderError(props.meta.error) : ''}
        </div>);
    };
    return InputWithValidation;
});
