import React, { useEffect } from 'react';

function Modal({ show, onClose, title, children, size = 'md', backdrop = true }) {
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && show) {
                onClose();
            }
        };

        if (show) {
            document.addEventListener('keydown', handleEscape);
            document.body.classList.add('modal-open');
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.classList.remove('modal-open');
        };
    }, [show, onClose]);

    if (!show) {
        return null;
    }

    const handleBackdropClick = (e) => {
        if (backdrop && e.target === e.currentTarget) {
            onClose();
        }
    };

    let sizeClass = '';
    if (size === 'sm') sizeClass = 'modal-sm';
    if (size === 'lg') sizeClass = 'modal-lg';
    if (size === 'xl') sizeClass = 'modal-xl';

    return (
        <div 
            className="modal fade show d-block" 
            tabIndex={-1}
            style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
            onClick={handleBackdropClick}
        >
            <div className={`modal-dialog ${sizeClass}`}>
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">{title}</h5>
                        <button
                            type="button"
                            className="btn-close"
                            onClick={onClose}
                            aria-label="Close"
                        />
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}

export default Modal;
