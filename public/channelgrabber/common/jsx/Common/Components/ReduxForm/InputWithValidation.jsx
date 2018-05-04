define([
    'react'
], function(
    React
) {
    var InputWithValidation = function(props) {
        var name = props.input.name;
        var onBlur = props.input.onBlur;
        var onFocus = props.input.onFocus;
        var onChange = props.input.onChange;
        var onDragStart = props.input.onDragStart;
        var onDrop = props.input.onDrop;
        var value = props.input.value;
        var type = props.type;
        return (<div>
            <div className="o-container-wrap">
                <input
                    name={name}
                    onBlur={onBlur}
                    onFocus={onFocus}
                    onChange={onChange}
                    onDragStart={onDragStart}
                    onDrop={onDrop}
                    value={value}
                    type={type}
                    className={"c-input-field"}
                />
            </div>
            <div className="o-container-wrap o-container-wrap--left u-color-red">
                {props.meta.touched && props.meta.error}
            </div>
        </div>);
    }
    return InputWithValidation;
});
