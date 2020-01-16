import React, { useState } from 'react';

const Textarea = (props) => {
    const [value, setValue] = useState('');

    const handleChange = (event) => {
        setValue(event.target.value);
    };

    return (
        <textarea
            id={props.id}
            name={props.id}
            value={value}
            onChange={handleChange}
            className={props.className}
            placeholder={props.placeholder}
        />
    );
}

export default Textarea;