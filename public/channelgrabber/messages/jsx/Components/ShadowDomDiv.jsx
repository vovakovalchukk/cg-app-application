import React, {useEffect} from 'react';

const ShadowDomDiv = (props) => {
    let shadowDiv = React.createRef();

    useEffect(() => {
        let shadow = shadowDiv.current.attachShadow({
            mode: 'closed'
        });
        shadow.innerHTML = props.body;
    });

    return (
        <div ref={shadowDiv} />
    )
};

export default ShadowDomDiv;