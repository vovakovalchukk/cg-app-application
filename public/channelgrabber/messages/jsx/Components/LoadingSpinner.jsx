import React from 'react';
import styled from 'styled-components';

const Container = styled.div`
    clear: both;
    border: 2px solid #5fafda;
    border-radius: 5px;
    background-clip: padding-box;
    position: fixed;
    color: #5fafda;
    background-color: #ffffff;
    padding: 20px 15px 15px 15px;
    text-align: left;
    margin: 0 auto;
    width: auto;
    height: auto !important;
    left: 50%;
    top: 50%;
    z-index: 100;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
`;

const LoadingSpinner = () => {
    return (
        <Container>
            <span className="indicator-wrapper -default u-margin-right-small">
                <svg viewBox="0 0 38 38" className="loading-indicator-element" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="indicator-gradient">
                            <stop stop-color="#5fafda" stop-opacity="0" offset="0%"></stop>
                            <stop stop-color="#5fafda" stop-opacity=".631" offset="63.146%"></stop>
                            <stop stop-color="#5fafda" offset="100%"></stop>
                        </linearGradient>
                    </defs>
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)">
                            <path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke-width="2" stroke="url(#indicator-gradient)" transform="rotate(198.457 18 18)">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"></animateTransform>
                            </path>
                            <circle cx="36" cy="18" r="1" transform="rotate(198.457 18 18)">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"></animateTransform>
                            </circle>
                        </g>
                    </g>
                </svg>
               </span>
            <span>
                Loading...
            </span>
        </Container>
    )
}

export default LoadingSpinner;
