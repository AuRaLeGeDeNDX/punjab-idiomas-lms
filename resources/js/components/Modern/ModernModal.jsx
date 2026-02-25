import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';

/**
 * Modern Modal with Glassmorphism
 * BACKDROP BLUR, SMOOTH ANIMATIONS, DRAMATIC ENTRANCE
 */
const ModernModal = ({ 
    isOpen, 
    onClose, 
    title, 
    children,
    size = 'md',
    showCloseButton = true 
}) => {
    const sizes = {
        sm: '400px',
        md: '600px',
        lg: '800px',
        xl: '1000px'
    };

    return (
        <AnimatePresence>
            {isOpen && (
                <>
                    {/* Backdrop */}
                    <motion.div
                        style={{
                            position: 'fixed',
                            inset: 0,
                            background: 'rgba(0, 0, 0, 0.5)',
                            backdropFilter: 'blur(8px)',
                            zIndex: 1000
                        }}
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        onClick={onClose}
                    />

                    {/* Modal */}
                    <div
                        style={{
                            position: 'fixed',
                            inset: 0,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            zIndex: 1001,
                            padding: '2rem'
                        }}
                    >
                        <motion.div
                            style={{
                                background: 'rgba(255, 255, 255, 0.95)',
                                backdropFilter: 'blur(20px)',
                                borderRadius: '24px',
                                boxShadow: '0 24px 64px rgba(0, 0, 0, 0.2)',
                                maxWidth: sizes[size],
                                width: '100%',
                                maxHeight: '90vh',
                                overflow: 'hidden',
                                border: '1px solid rgba(255, 255, 255, 0.3)'
                            }}
                            initial={{ opacity: 0, scale: 0.8, y: 50 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            exit={{ opacity: 0, scale: 0.8, y: 50 }}
                            transition={{ 
                                type: "spring", 
                                stiffness: 300, 
                                damping: 30 
                            }}
                        >
                            {/* Header */}
                            {title && (
                                <div style={{
                                    padding: '1.5rem 2rem',
                                    borderBottom: '1px solid rgba(0, 0, 0, 0.1)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'space-between',
                                    background: 'linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%)'
                                }}>
                                    <motion.h3
                                        style={{
                                            margin: 0,
                                            fontSize: '1.5rem',
                                            fontWeight: '700',
                                            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                            WebkitBackgroundClip: 'text',
                                            WebkitTextFillColor: 'transparent'
                                        }}
                                        initial={{ x: -20, opacity: 0 }}
                                        animate={{ x: 0, opacity: 1 }}
                                        transition={{ delay: 0.1 }}
                                    >
                                        {title}
                                    </motion.h3>

                                    {showCloseButton && (
                                        <motion.button
                                            onClick={onClose}
                                            style={{
                                                background: 'none',
                                                border: 'none',
                                                fontSize: '1.5rem',
                                                cursor: 'pointer',
                                                color: '#667eea',
                                                padding: '0.5rem',
                                                borderRadius: '8px',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center'
                                            }}
                                            whileHover={{ 
                                                scale: 1.1,
                                                rotate: 90,
                                                background: 'rgba(102, 126, 234, 0.1)'
                                            }}
                                            whileTap={{ scale: 0.9 }}
                                        >
                                            <i className="fas fa-times"></i>
                                        </motion.button>
                                    )}
                                </div>
                            )}

                            {/* Body */}
                            <motion.div
                                style={{
                                    padding: '2rem',
                                    overflowY: 'auto',
                                    maxHeight: 'calc(90vh - 100px)'
                                }}
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.2 }}
                            >
                                {children}
                            </motion.div>
                        </motion.div>
                    </div>
                </>
            )}
        </AnimatePresence>
    );
};

export default ModernModal;
