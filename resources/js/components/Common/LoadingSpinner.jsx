import React from 'react';

const LoadingSpinner = ({ size = 'md', className = '', message = 'Loading...' }) => {
    const sizeClass = {
        sm: 'spinner-border-sm',
        md: '',
        lg: 'spinner-border-lg'
    }[size];

    return (
        <div className="d-flex flex-column align-items-center justify-content-center p-5">
            <div 
                className={`spinner-border ${sizeClass} ${className}`} 
                role="status"
            >
                <span className="visually-hidden">{message}</span>
            </div>
            {message && <p className="mt-3 text-muted">{message}</p>}
        </div>
    );
};

export default LoadingSpinner;