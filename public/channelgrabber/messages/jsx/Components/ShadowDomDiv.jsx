import React, {useEffect} from 'react';

const ShadowDomDiv = (props) => {
    const shadowDiv = React.createRef();

    useEffect(() => {
        const shadow = shadowDiv.current.attachShadow({
            mode: 'closed'
        });
        shadow.innerHTML = props.body;
    });

    return (
        <div ref={shadowDiv} />
    )
};

export default ShadowDomDiv;