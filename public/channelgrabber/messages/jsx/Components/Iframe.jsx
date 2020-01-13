import React, {useEffect} from 'react';

const Iframe = (props) => {
    const iframe = React.createRef();

    useEffect(() => {
        window.setTimeout(() => {
            if ( iframe.current && iframe.current.contentWindow.document.body ){
                iframe.current.height = iframe.current.contentWindow.document.body.scrollHeight + 40 + 'px';
            }
        }, 100)
    });

    return (
        <iframe
            ref={iframe}
            srcDoc={props.body}
            style={{
                width: 640,
                border: `none`,
            }}
        />
    )
};

export default Iframe;